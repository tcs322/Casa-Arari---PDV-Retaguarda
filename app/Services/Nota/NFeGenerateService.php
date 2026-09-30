<?php

namespace App\Services\Nota;

use App\Enums\FormaPagamentoEnum;
use App\Enums\NumeroBandeiraCartaoEnum;
use App\Models\Venda;
use Carbon\Carbon;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use Illuminate\Support\Facades\Log;

class NFeGenerateService
{
    private $tools;
    private $config;

    public function __construct()
    {
        $this->initializeTools();
    }

    private function initializeTools()
    {
        $certificatePath = storage_path('app/' . config('nfe.certificado_path'));
        $certificatePassword = config('nfe.certificado_senha');

        $this->config = [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => (int) config('nfe.ambiente', 1), // Homologação
            "razaosocial" => config('nfe.razao_social'),
            "cnpj" => config('nfe.cnpj'),
            "siglaUF" => 'PA',
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => config('nfe.csc', ''),
            "CSCid" => config('nfe.csc_id', ''),
        ];

        $certificate = Certificate::readPfx(
            file_get_contents($certificatePath), 
            $certificatePassword
        );

        $this->tools = new Tools(json_encode($this->config), $certificate);
        $this->tools->model(65);
    }

    public function emitirNFe(Venda $venda): array
    {
        try {
            Log::info('🔧 Iniciando emissão de NF-e para venda: ' . $venda->uuid);

            // 1️⃣ Gerar XML
            $xml = $this->gerarXml($venda);
            Log::info('📄 XML gerado para venda: ' . $venda->uuid);
            Log::debug("Conteúdo do XML:", ['xml' => $xml]);

            // 2️⃣ Assinar XML
            $xmlAssinado = $this->assinarXml($xml);
            Log::info('✅ XML assinado com sucesso');

            // 3️⃣ Enviar para SEFAZ
            $sefazService = new SefaApiService();
            $resultado = $sefazService->autorizarNFe($xmlAssinado);

            // 🔍 Determina o tipo da resposta da SEFAZ
            if (($resultado['success'] ?? false) === true) {
                $tipo = 'autorizada';
            } elseif (($resultado['codigo_erro'] ?? '') === 'CONTINGENCIA' || ($resultado['modo_contingencia'] ?? false) === true) {
                $tipo = 'contingencia';
            } else {
                $tipo = 'rejeitada';
            }

            switch ($tipo) {
                // ✅ NF-e AUTORIZADA
                case 'autorizada':
                    $venda->update([
                        'status' => 'finalizada',
                        'status_nfe' => 'autorizada',
                        'chave_acesso_nfe' => $resultado['chave_acesso'],
                        'protocolo_nfe' => $resultado['numero_protocolo'],
                        'data_autorizacao_nfe' => now(),
                        'xml_nfe' => $xmlAssinado,
                        'xml_autorizado' => $resultado['xml'] ?? null,
                        'erro_nfe' => null,
                    ]);

                    Log::info("🎯 NF-e AUTORIZADA - Venda: {$venda->uuid}", [
                        'chave' => $resultado['chave_acesso'] ?? 'N/A',
                        'protocolo' => $resultado['numero_protocolo'] ?? 'N/A',
                        'numero_nota' => $venda->numero_nota_fiscal,
                    ]);

                    return [
                        'success' => true,
                        'tipo' => 'autorizada',
                        'mensagem' => 'NF-e autorizada com sucesso',
                        'chave_acesso' => $resultado['chave_acesso'] ?? null,
                        'numero_protocolo' => $resultado['numero_protocolo'] ?? null,
                        'numero_nota' => $venda->numero_nota_fiscal,
                        'xml' => $resultado['xml'] ?? $xmlAssinado,
                    ];

                // ⚙️ NF-e EM CONTINGÊNCIA
                case 'contingencia':
                    $venda->update([
                        'status' => 'finalizada',
                        'status_nfe' => 'contingencia',
                        'xml_nfe' => $xmlAssinado,
                        'erro_nfe' => $resultado['erro'] ?? 'SEFAZ indisponível',
                    ]);

                    Log::warning("⚙️ NF-e EMITIDA EM CONTINGÊNCIA - Venda: {$venda->uuid}", [
                        'erro' => $resultado['erro'] ?? 'SEFAZ indisponível',
                        'codigo' => $resultado['codigo_erro'] ?? 'CONTINGENCIA',
                    ]);

                    return [
                        'success' => false,
                        'tipo' => 'contingencia',
                        'mensagem' => 'SEFAZ indisponível — emissão em contingência necessária.',
                        'erro' => $resultado['erro'] ?? 'SEFAZ fora do ar',
                        'codigo_erro' => $resultado['codigo_erro'] ?? 'CONTINGENCIA',
                    ];

                // ❌ NF-e REJEITADA
                case 'rejeitada':
                default:
                    $venda->update([
                        'status' => 'pendente',
                        'status_nfe' => 'rejeitada',
                        'xml_nfe' => $xmlAssinado,
                        'erro_nfe' => $resultado['erro'] ?? 'Rejeição não especificada',
                    ]);

                    Log::error("❌ NF-e REJEITADA - Venda: {$venda->uuid}", [
                        'erro' => $resultado['erro'] ?? 'Desconhecido',
                        'codigo' => $resultado['codigo_erro'] ?? 'N/A',
                    ]);

                    return [
                        'success' => false,
                        'tipo' => 'rejeitada',
                        'mensagem' => 'NF-e rejeitada pela SEFAZ',
                        'erro' => $resultado['erro'] ?? 'Rejeição não especificada',
                        'codigo_erro' => $resultado['codigo_erro'] ?? null,
                    ];
            }

        } catch (\Exception $e) {
            Log::error('❌ Erro na emissão de NF-e para venda ' . $venda->uuid . ': ' . $e->getMessage());

            $venda->update([
                'status_nfe' => 'erro',
                'erro_nfe' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'tipo' => 'erro',
                'erro' => $e->getMessage(),
                'mensagem' => 'Falha interna na emissão da NF-e',
            ];
        }
    }

    /** * Gera XML da NF-e para uma emissão normal */ 
    public function gerarXml(Venda $venda): string 
    { 
        $cNF = $this->gerarCodigoNumerico(); 
        $dataEmissao = now(); 
        $chaveAcesso = $this->gerarChaveAcesso( $venda, $cNF, $dataEmissao ); 
        $cDV = $this->calcularDigitoVerificador( substr($chaveAcesso, 0, 43) ); 
        $xml = $this->montarEstruturaXml( $venda, $chaveAcesso, $cDV, $cNF, $dataEmissao ); 
        file_put_contents( storage_path('logs/xml_nfephp_make.xml'), $xml ); 
        return $xml; 
    }

    /** * Gera XML para reconstrução de uma NF-e histórica. 
     * * * IMPORTANTE: * 
     * - Mantém número da nota original * 
     * - Mantém série original * 
     * - Mantém data/hora original * 
     * - Utiliza o AAMM da data original na chave * 
     * - Não cria uma nova numeração fiscal */ 
    
    public function gerarXmlHistorico(Venda $venda): string 
    { 
        if (!$venda->data_venda) { 
            throw new \RuntimeException( "A venda {$venda->uuid} não possui data_venda." ); 
        } if (!$venda->numero_nota_fiscal) { 
            throw new \RuntimeException( "A venda {$venda->uuid} não possui numero_nota_fiscal." ); 
        } if (!$venda->serie_nfe) { 
            throw new \RuntimeException( "A venda {$venda->uuid} não possui serie_nfe." ); 
        } 
        
        $dataEmissao = $venda->data_venda; 
        Log::info( "🔄 Reconstruindo NF-e histórica", 
            [ 
                'venda_uuid' => $venda->uuid, 
                'numero_nota' => $venda->numero_nota_fiscal, 
                'serie' => $venda->serie_nfe, 
                'data_original' => $dataEmissao->format('Y-m-d H:i:s'), 
            ] ); 
            
        // O cNF pode ser regenerado para a reconstrução. 
        $cNF = $this->gerarCodigoNumerico(); 
        
        /* * IMPORTANTE: * A chave será construída utilizando o AAMM da data 
        * original da venda. */ 
        
        $chaveAcesso = $this->gerarChaveAcesso( $venda, $cNF, $dataEmissao ); 
        $cDV = $this->calcularDigitoVerificador( substr($chaveAcesso, 0, 43) ); 
        $xml = $this->montarEstruturaXml( $venda, $chaveAcesso, $cDV, $cNF, $dataEmissao ); 
        
        Log::info( "✅ XML histórico reconstruído", 
            [ 
                'venda_uuid' => $venda->uuid, 
                'numero_nota' => $venda->numero_nota_fiscal, 
                'serie' => $venda->serie_nfe, 
                'chave' => $chaveAcesso, 
                'data_emissao' => $dataEmissao->format('Y-m-d H:i:s'), 
            ] ); 
            
        return $xml; 
    }

    /**
     * Gera chave de acesso para a NF-e
     */
    // private function gerarChaveAcesso(Venda $venda, string $cNF): string
    // {
    //     Log::info("🔍 VERIFICANDO COMPOSIÇÃO DA CHAVE:");
        
    //     $campos = [
    //         'cUF' => '15',
    //         'AAMM' => date('ym'),
    //         'CNPJ' => config('nfe.cnpj'),
    //         'MOD' => '65',
    //         'SERIE' => str_pad($venda->serie_nfe ?? '1', 3, '0', STR_PAD_LEFT),
    //         'nNF' => str_pad($venda->numero_nota_fiscal ?? '1', 9, '0', STR_PAD_LEFT),
    //         'TPEMIS' => '1',
    //         'cNF' => $cNF
    //     ];
    
    //     $chaveSemDV = implode('', $campos);
    
    //     // Calcular DV
    //     $dv = $this->calcularDigitoVerificador($chaveSemDV);
        
    //     return $chaveSemDV . $dv;
    // }

    /** * Gera chave de acesso para a NF-e */ 
    private function gerarChaveAcesso( Venda $venda, string $cNF, Carbon $dataEmissao ): string 
    { 
        Log::info("🔍 VERIFICANDO COMPOSIÇÃO DA CHAVE:"); 
        $campos = [ 
            'cUF' => '15', 
            'AAMM' => $dataEmissao->format('ym'), 
            'CNPJ' => config('nfe.cnpj'), 
            'MOD' => '65', 
            'SERIE' => str_pad( $venda->serie_nfe, 3, '0', STR_PAD_LEFT ), 
            'nNF' => str_pad( $venda->numero_nota_fiscal, 9, '0', STR_PAD_LEFT ), 
            'TPEMIS' => '1', 
            'cNF' => $cNF, 
        ]; 
        
        $chaveSemDV = implode('', $campos); 
        $dv = $this->calcularDigitoVerificador($chaveSemDV); 
        
        Log::info( "🔑 Chave de acesso gerada", [ 'cUF' => $campos['cUF'], 'AAMM' => $campos['AAMM'], 'CNPJ' => $campos['CNPJ'], 'MOD' => $campos['MOD'], 'SERIE' => $campos['SERIE'], 'nNF' => $campos['nNF'], 'tpEmis' => $campos['TPEMIS'], 'cNF' => $campos['cNF'], 'DV' => $dv, ] ); 
        
        return $chaveSemDV . $dv; 
    }

    private function proximoNumeroNota()
    {
        $ultimaNFe = Venda::whereNotNull('numero_nota_fiscal')
                          ->orderBy('created_at', 'desc')
                          ->first();
        
        return $ultimaNFe ? intval($ultimaNFe->numero_nota_fiscal) + 1 : 1;
    }

    /**
     * Calcula dígito verificador da chave de acesso
     */
    private function calcularDigitoVerificador(string $chave): string
    {
        $pesos = [2, 3, 4, 5, 6, 7, 8, 9];
        $soma = 0;
        $contador = 0;
        
        // Percorrer a chave de trás para frente
        for ($i = strlen($chave) - 1; $i >= 0; $i--) {
            $soma += intval($chave[$i]) * $pesos[$contador % count($pesos)];
            $contador++;
        }
        
        $resto = $soma % 11;
        $dv = ($resto == 0 || $resto == 1) ? 0 : 11 - $resto;
        
        Log::info("Cálculo DV: Soma={$soma}, Resto={$resto}, DV={$dv}");
        
        return (string)$dv;
    }

    /**
     * Gera código numérico aleatório de 8 dígitos
     */
    private function gerarCodigoNumerico(): string
    {
        return str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Monta a estrutura completa do XML
     */
    private function montarEstruturaXml(Venda $venda, string $chaveAcesso, string $cDV, string $cNF, Carbon $dataEmissao): string
    {
        $emitente = $this->getEmitente();
        $destinatario = $this->getDestinatario($venda);
        $produtos = $this->getProdutos($venda);
        $total = $this->getTotal($venda);
        $pagamento = $this->gerarPagamento($venda);

        $tpAmb = (int) config('nfe.ambiente', 1);

        $infCpl = '';

        if ($tpAmb === 2) {
            $infCpl = 'NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL';
        }

        $infAdicXml = '';

        if (!empty($infCpl)) {
            $infAdicXml = <<<XML
            <infAdic>
                <infCpl>{$infCpl}</infCpl>
            </infAdic>
            XML;
        }

        // Monta os <detPag> dinamicamente a partir do array retornado por gerarPagamento()
        $detPagXml = '';

        foreach ($pagamento['detPag'] as $det) {
            $cardXml = '';

            if (isset($det['card'])) {
                $cardXml = "
                        <card>
                            <tpIntegra>{$det['card']['tpIntegra']}</tpIntegra>
                            <tBand>{$det['card']['tBand']}</tBand>
                        </card>";
            }

            $detPagXml .= "
                    <detPag>
                        <indPag>{$det['indPag']}</indPag>
                        <tPag>{$det['tPag']}</tPag>
                        <vPag>{$det['vPag']}</vPag>
                        {$cardXml}
                    </detPag>";
        }

        if ($destinatario['nome'] == 'CONSUMIDOR FINAL NAO INFORMADO')
        {
            // 🔹 Estrutura principal do XML
            $xml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <NFe xmlns="http://www.portalfiscal.inf.br/nfe">
            <infNFe versao="4.00" Id="NFe{$chaveAcesso}">
                <ide>
                    <cUF>15</cUF>
                    <cNF>{$cNF}</cNF>
                    <natOp>Venda de mercadoria</natOp>
                    <mod>65</mod>
                    <serie>{$venda->serie_nfe}</serie>
                    <nNF>{$venda->numero_nota_fiscal}</nNF>
                    <dhEmi>{$dataEmissao->format('Y-m-d\TH:i:sP')}</dhEmi>
                    <tpNF>1</tpNF>
                    <idDest>1</idDest>
                    <cMunFG>1501402</cMunFG>
                    <tpImp>4</tpImp>
                    <tpEmis>1</tpEmis>
                    <cDV>{$cDV}</cDV>
                    <tpAmb>{$tpAmb}</tpAmb>
                    <finNFe>1</finNFe>
                    <indFinal>1</indFinal>
                    <indPres>1</indPres>
                    <procEmi>0</procEmi>
                    <verProc>1.0</verProc>
                </ide>
                <emit>
                    <CNPJ>{$emitente['cnpj']}</CNPJ>
                    <xNome>{$emitente['razao_social']}</xNome>
                    <xFant>{$emitente['nome_fantasia']}</xFant>
                    <enderEmit>
                        <xLgr>{$emitente['endereco']['logradouro']}</xLgr>
                        <nro>{$emitente['endereco']['numero']}</nro>
                        <xBairro>{$emitente['endereco']['bairro']}</xBairro>
                        <cMun>{$emitente['endereco']['codigo_municipio']}</cMun>
                        <xMun>{$emitente['endereco']['municipio']}</xMun>
                        <UF>{$emitente['endereco']['uf']}</UF>
                        <CEP>{$emitente['endereco']['cep']}</CEP>
                        <cPais>1058</cPais>
                        <xPais>BRASIL</xPais>
                        <fone>{$emitente['endereco']['telefone']}</fone>
                    </enderEmit>
                    <IE>{$emitente['ie']}</IE>
                    <CRT>{$emitente['crt']}</CRT>
                </emit>
                {$produtos}
                <total>
                    <ICMSTot>
                        <vBC>0.00</vBC>
                        <vICMS>0.00</vICMS>
                        <vICMSDeson>0.00</vICMSDeson>
                        <vFCP>0.00</vFCP>
                        <vBCST>0.00</vBCST>
                        <vST>0.00</vST>
                        <vFCPST>0.00</vFCPST>
                        <vFCPSTRet>0.00</vFCPSTRet>
                        <vProd>{$total['valor_produtos']}</vProd>
                        <vFrete>0.00</vFrete>
                        <vSeg>0.00</vSeg>
                        <vDesc>0.00</vDesc>
                        <vII>0.00</vII>
                        <vIPI>0.00</vIPI>
                        <vIPIDevol>0.00</vIPIDevol>
                        <vPIS>{$total['valor_pis']}</vPIS>
                        <vCOFINS>{$total['valor_cofins']}</vCOFINS>
                        <vOutro>0.00</vOutro>
                        <vNF>{$total['valor_total']}</vNF>
                        <vTotTrib>0.00</vTotTrib>
                    </ICMSTot>
                </total>
                <transp>
                    <modFrete>9</modFrete>
                </transp>
                <pag>
                {$detPagXml}
                </pag>
                {$infAdicXml}
            </infNFe>
            </NFe>
            XML;
        } else {
            // 🔹 Estrutura principal do XML
            $xml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <NFe xmlns="http://www.portalfiscal.inf.br/nfe">
            <infNFe versao="4.00" Id="NFe{$chaveAcesso}">
                <ide>
                    <cUF>15</cUF>
                    <cNF>{$cNF}</cNF>
                    <natOp>Venda de mercadoria</natOp>
                    <mod>65</mod>
                    <serie>{$venda->serie_nfe}</serie>
                    <nNF>{$venda->numero_nota_fiscal}</nNF>
                    <dhEmi>{$dataEmissao->format('Y-m-d\TH:i:sP')}</dhEmi>
                    <tpNF>1</tpNF>
                    <idDest>1</idDest>
                    <cMunFG>1501402</cMunFG>
                    <tpImp>4</tpImp>
                    <tpEmis>1</tpEmis>
                    <cDV>{$cDV}</cDV>
                    <tpAmb>{$tpAmb}</tpAmb>
                    <finNFe>1</finNFe>
                    <indFinal>1</indFinal>
                    <indPres>1</indPres>
                    <procEmi>0</procEmi>
                    <verProc>1.0</verProc>
                </ide>
                <emit>
                    <CNPJ>{$emitente['cnpj']}</CNPJ>
                    <xNome>{$emitente['razao_social']}</xNome>
                    <xFant>{$emitente['nome_fantasia']}</xFant>
                    <enderEmit>
                        <xLgr>{$emitente['endereco']['logradouro']}</xLgr>
                        <nro>{$emitente['endereco']['numero']}</nro>
                        <xBairro>{$emitente['endereco']['bairro']}</xBairro>
                        <cMun>{$emitente['endereco']['codigo_municipio']}</cMun>
                        <xMun>{$emitente['endereco']['municipio']}</xMun>
                        <UF>{$emitente['endereco']['uf']}</UF>
                        <CEP>{$emitente['endereco']['cep']}</CEP>
                        <cPais>1058</cPais>
                        <xPais>BRASIL</xPais>
                        <fone>{$emitente['endereco']['telefone']}</fone>
                    </enderEmit>
                    <IE>{$emitente['ie']}</IE>
                    <CRT>{$emitente['crt']}</CRT>
                </emit>
                <dest>
                    <CPF>{$destinatario['cpf']}</CPF>
                    <xNome>{$destinatario['nome']}</xNome>
                    <enderDest>
                        <xLgr>{$destinatario['endereco']['logradouro']}</xLgr>
                        <nro>{$destinatario['endereco']['numero']}</nro>
                        <xBairro>{$destinatario['endereco']['bairro']}</xBairro>
                        <cMun>{$destinatario['endereco']['codigo_municipio']}</cMun>
                        <xMun>{$destinatario['endereco']['municipio']}</xMun>
                        <UF>{$destinatario['endereco']['uf']}</UF>
                        <CEP>{$destinatario['endereco']['cep']}</CEP>
                        <cPais>1058</cPais>
                        <xPais>BRASIL</xPais>
                    </enderDest>
                    <indIEDest>9</indIEDest>
                </dest>
                {$produtos}
                <total>
                    <ICMSTot>
                        <vBC>0.00</vBC>
                        <vICMS>0.00</vICMS>
                        <vICMSDeson>0.00</vICMSDeson>
                        <vFCP>0.00</vFCP>
                        <vBCST>0.00</vBCST>
                        <vST>0.00</vST>
                        <vFCPST>0.00</vFCPST>
                        <vFCPSTRet>0.00</vFCPSTRet>
                        <vProd>{$total['valor_produtos']}</vProd>
                        <vFrete>0.00</vFrete>
                        <vSeg>0.00</vSeg>
                        <vDesc>0.00</vDesc>
                        <vII>0.00</vII>
                        <vIPI>0.00</vIPI>
                        <vIPIDevol>0.00</vIPIDevol>
                        <vPIS>{$total['valor_pis']}</vPIS>
                        <vCOFINS>{$total['valor_cofins']}</vCOFINS>
                        <vOutro>0.00</vOutro>
                        <vNF>{$total['valor_total']}</vNF>
                        <vTotTrib>0.00</vTotTrib>
                    </ICMSTot>
                </total>
                <transp>
                    <modFrete>9</modFrete>
                </transp>
                <pag>
                {$detPagXml}
                </pag>
                {$infAdicXml}
            </infNFe>
            </NFe>
            XML;
        }

        return $xml;
    }


    /**
     * Retorna dados do emitente
     */
    private function getEmitente(): array
    {
        return [
            'cnpj' => config('nfe.cnpj'),
            'razao_social' => config('nfe.razao_social'),
            'nome_fantasia' => config('nfe.nome_fantasia', 'Livraria & Café PA'),
            'ie' => config('nfe.ie', '750432209'),
            'crt' => '1', // Simples Nacional
            'endereco' => [
                'logradouro' => config('nfe.logradouro'),
                'numero' => config('nfe.numero'),
                'bairro' => config('nfe.bairro'),
                'codigo_municipio' => config('nfe.codigo_municipio'),
                'municipio' => config('nfe.municipio'),
                'uf' => config('nfe.uf'),
                'cep' => config('nfe.cep'),
                'telefone' => config('nfe.telefone')
            ]
        ];
    }

    /**
     * Retorna dados do destinatário baseado na venda
     */
    private function getDestinatario(Venda $venda): array
    {
        $ambiente = config('nfe.ambiente', 2); // 2 = Homologação
        
        if ($ambiente == 2) {
            // ✅ HOMOLOGAÇÃO: nome fixo
            $nomeDestinatario = 'NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL';
            $enderecoHomologacao = [
                'logradouro' => 'Rua Teste',
                'numero' => '100', 
                'bairro' => 'Centro',
                'codigo_municipio' => '1501402',
                'municipio' => 'BELEM',
                'uf' => 'PA',
                'cep' => '66000000'
            ];
        } else {
            // ✅ PRODUÇÃO: dados reais do cliente
            $nomeDestinatario = $venda->cliente->nome ?? 'Consumidor Final';
            $enderecoHomologacao = [
                'logradouro' => $venda->cliente->endereco->logradouro ?? 'Não informado',
                'numero' => $venda->cliente->endereco->numero ?? 'S/N',
                'bairro' => $venda->cliente->endereco->bairro ?? 'Centro',
                'codigo_municipio' => $venda->cliente->endereco->codigo_municipio ?? '1501402',
                'municipio' => $venda->cliente->endereco->cidade ?? 'BELEM',
                'uf' => $venda->cliente->endereco->uf ?? 'PA',
                'cep' => $venda->cliente->endereco->cep ?? '66000000'
            ];
        }
        
        return [
            'cpf' => $venda->cliente->cpf ?? '12345678909',
            'nome' => $nomeDestinatario,
            'endereco' => $enderecoHomologacao
        ];
    }

    /**
     * Monta os produtos da venda (corrigido para apresentar vProd bruto e vDesc)
     */
    private function getProdutos(Venda $venda): string
    {
        $produtosXml = '';
        $itens = $venda->itens;

        foreach ($itens as $index => $item) {
            $nItem = $index + 1;

            // Preço unitário e quantidade com precisão adequada
            $precoUnitario = number_format((float)$item->preco_unitario, 2, '.', '');
            $quantidade = number_format((float)$item->quantidade, 4, '.', '');

            // Valor bruto do produto (sem desconto)
            $vProdBruto = number_format((float)$item->preco_unitario * (float)$item->quantidade, 2, '.', '');

            // Valor da base de cálculo (vBC)
            $vBC = number_format((float)$item->preco_unitario * (float)$item->quantidade, 2, '.', '');

            // Cálculo dos tributos
            $valorPIS = number_format((float)$vBC * 0.0165, 2, '.', '');
            $valorCOFINS = number_format((float)$vBC * 0.0760, 2, '.', '');


            $produtosXml .= <<<XML
            <det nItem="{$nItem}">
            <prod>
                <cProd>{$item->produto->codigo}</cProd>
                <cEAN>7890000000000</cEAN>
                <xProd>{$item->produto->nome_titulo}</xProd>
                <NCM>49019900</NCM>
                <CFOP>5102</CFOP>
                <uCom>UN</uCom>
                <qCom>{$quantidade}</qCom>
                <vUnCom>{$precoUnitario}</vUnCom>
                <vProd>{$vProdBruto}</vProd>
                <cEANTrib>7890000000000</cEANTrib>
                <uTrib>UN</uTrib>
                <qTrib>{$quantidade}</qTrib>
                <vUnTrib>{$precoUnitario}</vUnTrib>
                <indTot>1</indTot>
            </prod>
            <imposto>
                <vTotTrib>0.00</vTotTrib>
                <ICMS>
                    <ICMSSN102>
                        <orig>0</orig>
                        <CSOSN>102</CSOSN>
                    </ICMSSN102>
                </ICMS>
                <PIS>
                    <PISAliq>
                        <CST>01</CST>
                        <vBC>{$vBC}</vBC>
                        <pPIS>1.65</pPIS>
                        <vPIS>{$valorPIS}</vPIS>
                    </PISAliq>
                </PIS>
                <COFINS>
                    <COFINSAliq>
                        <CST>01</CST>
                        <vBC>{$vBC}</vBC>
                        <pCOFINS>7.60</pCOFINS>
                        <vCOFINS>{$valorCOFINS}</vCOFINS>
                    </COFINSAliq>
                </COFINS>
            </imposto>
            </det>
        XML;
        }

        return $produtosXml;
    }

    /**
     * Calcula totais da venda (soma por itens para manter coerência)
     */
    private function getTotal(Venda $venda): array
    {
        $valorProdutosBruto = 0.0;
        $valorDescontos = 0.0;
        $valorPIS = 0.0;
        $valorCOFINS = 0.0;

        foreach ($venda->itens as $item) {
            $vProdBruto = (float)$item->preco_unitario * (float)$item->quantidade;
            $vLiquido = (float)$item->preco_total; // já representa subtotal * quantidade (valor vendido)
            $vDescItem = max(0.0, $vProdBruto - $vLiquido);

            // Acumula
            $valorProdutosBruto += $vProdBruto;
            $valorDescontos += $vDescItem;

            // PIS/COFINS com base no valor líquido
            $valorPIS += round($vLiquido * 0.0165, 2);
            $valorCOFINS += round($vLiquido * 0.0760, 2);
        }

        // Valor final (vNF) — confiança no campo valor_total da venda (deve ser vProdBruto - descontos + frete/outros)
        $valorTotalNota = $valorProdutosBruto;

        return [
            'valor_produtos' => number_format($valorProdutosBruto, 2, '.', ''),
            'valor_descontos' => number_format($valorDescontos, 2, '.', ''),
            'valor_pis' => number_format($valorPIS, 2, '.', ''),
            'valor_cofins' => number_format($valorCOFINS, 2, '.', ''),
            'valor_total' => number_format($valorTotalNota, 2, '.', ''),
        ];
    }

    // public function reconstruirXmlHistorico(Venda $venda): string
    // {
    //     if (empty($venda->xml_nfe)) {
    //         throw new \RuntimeException(
    //             "A venda {$venda->uuid} não possui xml_nfe armazenado."
    //         );
    //     }

    //     $xmlOriginal = $venda->xml_nfe;

    //     $dom = new \DOMDocument('1.0', 'UTF-8');
    //     $dom->preserveWhiteSpace = false;
    //     $dom->formatOutput = true;

    //     libxml_use_internal_errors(true);

    //     if (!$dom->loadXML($xmlOriginal)) {
    //         $erros = libxml_get_errors();
    //         libxml_clear_errors();

    //         $mensagens = array_map(
    //             fn ($erro) => trim($erro->message),
    //             $erros
    //         );

    //         throw new \RuntimeException(
    //             'Não foi possível carregar o XML original: ' .
    //             implode(' | ', $mensagens)
    //         );
    //     }

    //     libxml_clear_errors();

    //     $xpath = new \DOMXPath($dom);

    //     $xpath->registerNamespace(
    //         'nfe',
    //         'http://www.portalfiscal.inf.br/nfe'
    //     );

    //     $xpath->registerNamespace(
    //         'ds',
    //         'http://www.w3.org/2000/09/xmldsig#'
    //     );

    //     /*
    //     * =========================================================
    //     * 1. CORRIGIR tpImp
    //     * =========================================================
    //     *
    //     * XML original:
    //     * <tpImp>1</tpImp>
    //     *
    //     * NFC-e modelo 65:
    //     * <tpImp>4</tpImp>
    //     */
    //     $tpImpNodes = $xpath->query('//nfe:infNFe/nfe:ide/nfe:tpImp');

    //     if ($tpImpNodes->length !== 1) {
    //         throw new \RuntimeException(
    //             "Não foi possível localizar exatamente uma tag <tpImp>."
    //         );
    //     }

    //     $tpImpNodes->item(0)->nodeValue = '4';

    //     /*
    //     * =========================================================
    //     * 2. CORRIGIR PAGAMENTO COM CARTÃO
    //     * =========================================================
    //     */
    //     $detPagNodes = $xpath->query('//nfe:infNFe/nfe:pag/nfe:detPag');

    //     foreach ($detPagNodes as $detPag) {
    //         $tPagNode = $xpath->query('./nfe:tPag', $detPag)->item(0);

    //         if (!$tPagNode) {
    //             continue;
    //         }

    //         $tPag = trim($tPagNode->nodeValue);

    //         // 03 = crédito
    //         // 04 = débito
    //         if (!in_array($tPag, ['03', '04'], true)) {
    //             continue;
    //         }

    //         /*
    //         * Se o XML já possuir <card>, não adicionamos novamente.
    //         */
    //         $cardExistente = $xpath->query('./nfe:card', $detPag);

    //         if ($cardExistente->length > 0) {
    //             continue;
    //         }

    //         $dadosCartao = $this->gerarDadosCartao($venda);

    //         $card = $dom->createElementNS(
    //             'http://www.portalfiscal.inf.br/nfe',
    //             'card'
    //         );

    //         $tpIntegra = $dom->createElementNS(
    //             'http://www.portalfiscal.inf.br/nfe',
    //             'tpIntegra',
    //             $dadosCartao['tpIntegra']
    //         );

    //         $tBand = $dom->createElementNS(
    //             'http://www.portalfiscal.inf.br/nfe',
    //             'tBand',
    //             $dadosCartao['tBand']
    //         );

    //         $card->appendChild($tpIntegra);
    //         $card->appendChild($tBand);

    //         $detPag->appendChild($card);
    //     }

    //     /*
    //     * =========================================================
    //     * 3. REMOVER INFORMAÇÕES DE HOMOLOGAÇÃO
    //     * =========================================================
    //     *
    //     * O XML histórico possui:
    //     *
    //     * <infAdic>
    //     *     <infCpl>
    //     *         NF-e emitida em ambiente de homologacao
    //     *     </infCpl>
    //     * </infAdic>
    //     *
    //     * Essa informação pertence ao contexto da emissão original
    //     * e não deve ser carregada para o novo XML.
    //     */
    //     $infAdicNodes = $xpath->query('//nfe:infNFe/nfe:infAdic');

    //     foreach ($infAdicNodes as $infAdic) {
    //         $infAdic->parentNode->removeChild($infAdic);
    //     }

    //     /*
    //     * =========================================================
    //     * 4. REMOVER ASSINATURA ANTIGA
    //     * =========================================================
    //     *
    //     * Qualquer alteração dentro de <infNFe> torna a assinatura
    //     * original inválida.
    //     *
    //     * Nesta primeira etapa NÃO assinaremos novamente.
    //     */
    //     $signatureNodes = $xpath->query('//ds:Signature');

    //     foreach ($signatureNodes as $signature) {
    //         $signature->parentNode->removeChild($signature);
    //     }

    //     /*
    //     * =========================================================
    //     * 5. NÃO ALTERAMOS:
    //     * =========================================================
    //     *
    //     * - Id da infNFe
    //     * - chave de acesso
    //     * - cNF
    //     * - cDV
    //     * - nNF
    //     * - série
    //     * - dhEmi
    //     * - produtos
    //     * - impostos
    //     * - totais
    //     * - destinatário
    //     * - emitente
    //     *
    //     * Eles permanecem exatamente como estavam no documento
    //     * histórico.
    //     */

    //     $xmlReconstruido = $dom->saveXML();

    //     if (!$xmlReconstruido) {
    //         throw new \RuntimeException(
    //             "Falha ao serializar o XML reconstruído."
    //         );
    //     }

    //     Log::info('XML histórico reconstruído a partir do xml_nfe original.', [
    //         'venda_id' => $venda->id,
    //         'venda_uuid' => $venda->uuid,
    //         'numero_nota' => $venda->numero_nota_fiscal,
    //         'serie' => $venda->serie_nfe,
    //     ]);

    //     return $xmlReconstruido;
    // }

    public function reconstruirXmlParaRegularizacao(Venda $venda): string
    {
        if (empty($venda->xml_nfe)) {
            throw new \RuntimeException(
                "A venda {$venda->uuid} não possui xml_nfe armazenado."
            );
        }

        $xmlOriginal = $venda->xml_nfe;

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        libxml_use_internal_errors(true);

        if (!$dom->loadXML($xmlOriginal)) {
            $erros = libxml_get_errors();
            libxml_clear_errors();

            $mensagens = array_map(
                fn ($erro) => trim($erro->message),
                $erros
            );

            throw new \RuntimeException(
                'Não foi possível carregar o XML original: ' .
                implode(' | ', $mensagens)
            );
        }

        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $namespaceNFe = 'http://www.portalfiscal.inf.br/nfe';

        $xpath->registerNamespace(
            'nfe',
            $namespaceNFe
        );

        $xpath->registerNamespace(
            'ds',
            'http://www.w3.org/2000/09/xmldsig#'
        );

        /*
        * =========================================================
        * 1. CORRIGIR tpImp
        * =========================================================
        *
        * NFC-e modelo 65:
        *
        * <tpImp>4</tpImp>
        */
        $tpImpNodes = $xpath->query(
            '//nfe:infNFe/nfe:ide/nfe:tpImp'
        );

        if ($tpImpNodes->length !== 1) {
            throw new \RuntimeException(
                'Não foi possível localizar exatamente uma tag <tpImp>.'
            );
        }

        $tpImpNodes->item(0)->nodeValue = '4';

        /*
        * =========================================================
        * 2. CORRIGIR PAGAMENTO COM CARTÃO
        * =========================================================
        */
        $detPagNodes = $xpath->query(
            '//nfe:infNFe/nfe:pag/nfe:detPag'
        );

        foreach ($detPagNodes as $detPag) {

            $tPagNode = $xpath->query(
                './nfe:tPag',
                $detPag
            )->item(0);

            if (!$tPagNode) {
                continue;
            }

            $tPag = trim($tPagNode->nodeValue);

            // 03 = crédito
            // 04 = débito
            if (!in_array($tPag, ['03', '04'], true)) {
                continue;
            }

            /*
            * Se o XML já possuir <card>, não adicionamos novamente.
            */
            $cardExistente = $xpath->query(
                './nfe:card',
                $detPag
            );

            if ($cardExistente->length > 0) {
                continue;
            }

            $dadosCartao = $this->gerarDadosCartao($venda);

            $card = $dom->createElementNS(
                $namespaceNFe,
                'card'
            );

            $tpIntegra = $dom->createElementNS(
                $namespaceNFe,
                'tpIntegra',
                $dadosCartao['tpIntegra']
            );

            $tBand = $dom->createElementNS(
                $namespaceNFe,
                'tBand',
                $dadosCartao['tBand']
            );

            $card->appendChild($tpIntegra);
            $card->appendChild($tBand);

            $detPag->appendChild($card);
        }

        /*
        * =========================================================
        * 3. REMOVER INFORMAÇÕES DE HOMOLOGAÇÃO
        * =========================================================
        */
        $infAdicNodes = $xpath->query(
            '//nfe:infNFe/nfe:infAdic'
        );

        foreach ($infAdicNodes as $infAdic) {
            $infAdic->parentNode->removeChild($infAdic);
        }

        /*
        * =========================================================
        * 4. REMOVER INFORMAÇÕES SUPLEMENTARES ANTIGAS
        * =========================================================
        *
        * O <infNFeSupl> pertence ao XML original e contém,
        * entre outras informações, o QR Code baseado na chave
        * de acesso original.
        *
        * Como estamos reconstruindo a NFC-e com uma nova chave,
        * essa estrutura não pode ser reaproveitada.
        *
        * Ela será recriada posteriormente pelo fluxo de geração/
        * assinatura, quando aplicável.
        */
        $infNFeSuplNodes = $xpath->query(
            '//nfe:infNFeSupl'
        );

        foreach ($infNFeSuplNodes as $infNFeSupl) {
            $infNFeSupl->parentNode->removeChild($infNFeSupl);
        }

        /*
        * =========================================================
        * 4. REMOVER ASSINATURA ANTIGA
        * =========================================================
        *
        * Qualquer alteração em <infNFe> invalida a assinatura
        * anterior.
        */
        $signatureNodes = $xpath->query(
            '//ds:Signature'
        );

        foreach ($signatureNodes as $signature) {
            $signature->parentNode->removeChild($signature);
        }

        /*
        * =========================================================
        * 5. OBTER DADOS DA NOVA EMISSÃO
        * =========================================================
        *
        * Os dados comerciais permanecem históricos.
        *
        * A data de emissão, entretanto, passa a representar
        * a nova emissão que será realizada agora.
        */
        $ideNode = $xpath->query(
            '//nfe:infNFe/nfe:ide'
        )->item(0);

        if (!$ideNode) {
            throw new \RuntimeException(
                'Não foi possível localizar a tag <ide>.'
            );
        }

        /*
        * ---------------------------------------------------------
        * Data/hora atual
        * ---------------------------------------------------------
        */
        $dataEmissao = now();

        $dhEmiNodes = $xpath->query(
            './nfe:dhEmi',
            $ideNode
        );

        if ($dhEmiNodes->length !== 1) {
            throw new \RuntimeException(
                'Não foi possível localizar exatamente uma tag <dhEmi>.'
            );
        }

        $dhEmiNodes->item(0)->nodeValue =
            $dataEmissao->format('Y-m-d\TH:i:sP');

        /*
        * =========================================================
        * 6. OBTER COMPONENTES DA CHAVE
        * =========================================================
        *
        * Mantemos os dados da venda/documento original:
        *
        * - cUF
        * - CNPJ
        * - modelo
        * - série
        * - nNF
        * - tpEmis
        * - cNF
        *
        * O AAMM será obtido da NOVA data.
        */
        $cUF = trim(
            $xpath->query(
                './nfe:cUF',
                $ideNode
            )->item(0)?->nodeValue ?? ''
        );

        $modelo = trim(
            $xpath->query(
                './nfe:mod',
                $ideNode
            )->item(0)?->nodeValue ?? ''
        );

        $serie = trim(
            $xpath->query(
                './nfe:serie',
                $ideNode
            )->item(0)?->nodeValue ?? ''
        );

        $nNF = trim(
            $xpath->query(
                './nfe:nNF',
                $ideNode
            )->item(0)?->nodeValue ?? ''
        );

        $tpEmis = trim(
            $xpath->query(
                './nfe:tpEmis',
                $ideNode
            )->item(0)?->nodeValue ?? ''
        );

        $cNF = trim(
            $xpath->query(
                './nfe:cNF',
                $ideNode
            )->item(0)?->nodeValue ?? ''
        );

        $cnpj = trim(
            $xpath->query(
                '//nfe:infNFe/nfe:emit/nfe:CNPJ'
            )->item(0)?->nodeValue ?? ''
        );

        /*
        * =========================================================
        * 7. VALIDAR COMPONENTES
        * =========================================================
        */
        if (
            $cUF === '' ||
            $modelo === '' ||
            $serie === '' ||
            $nNF === '' ||
            $tpEmis === '' ||
            $cNF === '' ||
            $cnpj === ''
        ) {
            throw new \RuntimeException(
                'Não foi possível obter todos os componentes necessários para gerar a nova chave de acesso.'
            );
        }

        /*
        * =========================================================
        * 8. MONTAR BASE DA NOVA CHAVE
        * =========================================================
        *
        * A chave possui:
        *
        * cUF + AAMM + CNPJ + mod + série + nNF +
        * tpEmis + cNF + cDV
        *
        * Neste momento ainda temos 43 dígitos.
        */
        $aamm = $dataEmissao->format('ym');

        $baseChave =
            str_pad($cUF, 2, '0', STR_PAD_LEFT) .
            $aamm .
            str_pad($cnpj, 14, '0', STR_PAD_LEFT) .
            str_pad($modelo, 2, '0', STR_PAD_LEFT) .
            str_pad($serie, 3, '0', STR_PAD_LEFT) .
            str_pad($nNF, 9, '0', STR_PAD_LEFT) .
            str_pad($tpEmis, 1, '0', STR_PAD_LEFT) .
            str_pad($cNF, 8, '0', STR_PAD_LEFT);

        if (!preg_match('/^\d{43}$/', $baseChave)) {
            throw new \RuntimeException(
                'A base da nova chave de acesso não possui exatamente 43 dígitos.'
            );
        }

        /*
        * =========================================================
        * 9. CALCULAR NOVO cDV
        * =========================================================
        */
        $peso = 2;
        $soma = 0;

        for ($i = strlen($baseChave) - 1; $i >= 0; $i--) {

            $soma += ((int) $baseChave[$i]) * $peso;

            $peso++;

            if ($peso > 9) {
                $peso = 2;
            }
        }

        $resto = $soma % 11;
        $novoCDV = 11 - $resto;

        if ($novoCDV >= 10) {
            $novoCDV = 0;
        }

        $novoCDV = (string) $novoCDV;

        /*
        * =========================================================
        * 10. MONTAR NOVA CHAVE COMPLETA
        * =========================================================
        */
        $novaChaveAcesso = $baseChave . $novoCDV;

        if (strlen($novaChaveAcesso) !== 44) {
            throw new \RuntimeException(
                'A nova chave de acesso não possui exatamente 44 dígitos.'
            );
        }

        /*
        * =========================================================
        * 11. ATUALIZAR cDV
        * =========================================================
        */
        $cDVNodes = $xpath->query(
            './nfe:cDV',
            $ideNode
        );

        if ($cDVNodes->length !== 1) {
            throw new \RuntimeException(
                'Não foi possível localizar exatamente uma tag <cDV>.'
            );
        }

        $cDVNodes->item(0)->nodeValue = $novoCDV;

        /*
        * =========================================================
        * 12. ATUALIZAR Id DA infNFe
        * =========================================================
        */
        $infNFeNodes = $xpath->query(
            '//nfe:infNFe'
        );

        if ($infNFeNodes->length !== 1) {
            throw new \RuntimeException(
                'Não foi possível localizar exatamente uma tag <infNFe>.'
            );
        }

        $infNFe = $infNFeNodes->item(0);

        if (!$infNFe instanceof \DOMElement) {
            throw new \RuntimeException(
                'O elemento <infNFe> localizado não é um DOMElement válido.'
            );
        }

        $infNFe->setAttribute(
            'Id',
            'NFe' . $novaChaveAcesso
        );

        /*
        * =========================================================
        * 13. SERIALIZAR XML
        * =========================================================
        */
        $xmlReconstruido = $dom->saveXML();

        if (!$xmlReconstruido) {
            throw new \RuntimeException(
                'Falha ao serializar o XML reconstruído para regularização.'
            );
        }

        /*
        * =========================================================
        * 14. LOG
        * =========================================================
        */
        Log::info(
            'XML reconstruído para regularização fiscal.',
            [
                'venda_id' => $venda->id,
                'venda_uuid' => $venda->uuid,
                'numero_nota' => $nNF,
                'serie' => $serie,
                'dhEmi' => $dataEmissao->format('Y-m-d\TH:i:sP'),
                'chave_nova' => $novaChaveAcesso,
                'cDV_novo' => $novoCDV,
                'cNF' => $cNF,
            ]
        );

        return $xmlReconstruido;
    }

    // private function gerarPagamento(Venda $venda)
    // {
    //     $formaPagamento = $this->mapearFormaPagamentoNFe($venda->forma_pagamento);

    //     // Define se o pagamento é à vista (0) ou a prazo (1)
    //     $indPag = ($formaPagamento == '03' && $venda->quantidade_parcelas > 1) ? '1' : '0';

    //     // return [
    //     //     'detPag' => [[
    //     //         'indPag' => $indPag,
    //     //         'tPag'   => $formaPagamento,
    //     //         'vPag'   => number_format($venda->valor_total, 2, '.', '')
    //     //     ]]
    //     // ];

    //     return [
    //         'detPag' => [[
    //             'indPag' => 0,
    //             'tPag'   => '01',
    //             'vPag'   => number_format($venda->valor_total, 2, '.', '')
    //         ]]
    //     ];
    // }

    private function gerarPagamento(Venda $venda): array
    {
        $formaPagamento = $this->mapearFormaPagamentoNFe($venda->forma_pagamento);

        $indPag = ($formaPagamento === '03' && $venda->quantidade_parcelas > 1)
            ? '1'
            : '0';

        $detPag = [
            'indPag' => $indPag,
            'tPag'   => $formaPagamento,
            'vPag'   => number_format($venda->valor_total, 2, '.', ''),
        ];

        if (in_array($formaPagamento, ['03', '04'], true)) {
            $detPag['card'] = $this->gerarDadosCartao($venda);
        }

        return [
            'detPag' => [$detPag],
        ];
    }

    private function gerarDadosCartao(Venda $venda): array
    {
        return [
            'tpIntegra' => '2',
            'tBand'     => NumeroBandeiraCartaoEnum::fromKey(
                $venda->bandeira_cartao
            )->value,
        ];
    }


    private function mapearFormaPagamentoNFe($formaPagamento)
    {
        $mapeamento = [
            FormaPagamentoEnum::DINHEIRO => '01',
            FormaPagamentoEnum::CARTAO_CREDITO => '03',
            FormaPagamentoEnum::CARTAO_DEBITO => '04', 
            FormaPagamentoEnum::PIX => '15'
        ];

        return $mapeamento[$formaPagamento] ?? '99';
    }

    /**
     * Retorna data e hora atual no formato correto
     */
    public function getDataHoraEmissao(): string
    {
        return date('Y-m-d\TH:i:sP');
    }

    /**
     * Assina o XML
     */
    private function assinarXml(string $xml): string
    {
        $certificatePath = storage_path('app/' . config('nfe.certificado_path'));
        $certificatePassword = config('nfe.certificado_senha');
        
        $config = [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => (int) config('nfe.ambiente', 1),
            "razaosocial" => config('nfe.razao_social'),
            "cnpj" => config('nfe.cnpj'),
            "siglaUF" => 'PA',
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => config('nfe.csc', ''),
            "CSCid" => config('nfe.csc_id', ''),
        ];
        
        $certificate = Certificate::readPfx(
            file_get_contents($certificatePath), 
            $certificatePassword
        );
        
        // Assinar o XML
        return $this->tools->signNFe($xml);
    }

    public function assinarXmlHistorico(string $xml): string
    {
        return $this->assinarXml($xml);
    }
}