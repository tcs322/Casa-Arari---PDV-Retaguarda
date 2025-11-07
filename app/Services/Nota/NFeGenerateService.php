<?php

namespace App\Services\Nota;

use App\Enums\FormaPagamentoEnum;
use App\Models\Venda;
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
            "tpAmb" => 2, // Homologação
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

    /**
     * Gera XML da NF-e a partir dos dados da venda
     */
    public function gerarXml(Venda $venda): string
    {
        $cNF = $this->gerarCodigoNumerico();
        $chaveAcesso = $this->gerarChaveAcesso($venda, $cNF);
        $cDV = $this->calcularDigitoVerificador(substr($chaveAcesso, 0, 43));
        
        // Montar XML baseado na estrutura que foi aceita
        $xml = $this->montarEstruturaXml($venda, $chaveAcesso, $cDV, $cNF);

        file_put_contents(storage_path('logs/xml_nfephp_make.xml'), $xml);

        return $xml;
    }

    /**
     * Gera chave de acesso para a NF-e
     */
    private function gerarChaveAcesso(Venda $venda, string $cNF): string
    {
        Log::info("🔍 VERIFICANDO COMPOSIÇÃO DA CHAVE:");
        
        $campos = [
            'cUF' => '15',
            'AAMM' => date('ym'),
            'CNPJ' => config('nfe.cnpj'),
            'MOD' => '55',
            'SERIE' => str_pad($venda->serie_nfe ?? '1', 3, '0', STR_PAD_LEFT),
            'nNF' => str_pad($venda->numero_nota_fiscal ?? '1', 9, '0', STR_PAD_LEFT),
            'TPEMIS' => '1',
            'cNF' => $cNF
        ];
    
        $chaveSemDV = implode('', $campos);
    
        // Calcular DV
        $dv = $this->calcularDigitoVerificador($chaveSemDV);
        
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
    private function montarEstruturaXml(Venda $venda, string $chaveAcesso, string $cDV, string $cNF): string
    {
        $emitente = $this->getEmitente();
        $destinatario = $this->getDestinatario($venda);
        $produtos = $this->getProdutos($venda);
        $total = $this->getTotal($venda);
        $pagamento = $this->gerarPagamento($venda);

        // 🔹 Monta os <detPag> dinamicamente a partir do array retornado por gerarPagamento()
        $detPagXml = '';
        foreach ($pagamento['detPag'] as $det) {
            $detPagXml .= "
            <detPag>
                <indPag>{$det['indPag']}</indPag>
                <tPag>{$det['tPag']}</tPag>
                <vPag>{$det['vPag']}</vPag>
            </detPag>";
        }

        // 🔹 Estrutura principal do XML
        $xml = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <NFe xmlns="http://www.portalfiscal.inf.br/nfe">
    <infNFe versao="4.00" Id="NFe{$chaveAcesso}">
        <ide>
            <cUF>15</cUF>
            <cNF>{$cNF}</cNF>
            <natOp>Venda de mercadoria</natOp>
            <mod>55</mod>
            <serie>{$venda->serie_nfe}</serie>
            <nNF>{$venda->numero_nota_fiscal}</nNF>
            <dhEmi>{$this->getDataHoraEmissao()}</dhEmi>
            <tpNF>1</tpNF>
            <idDest>1</idDest>
            <cMunFG>1501402</cMunFG>
            <tpImp>1</tpImp>
            <tpEmis>1</tpEmis>
            <cDV>{$cDV}</cDV>
            <tpAmb>2</tpAmb>
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
        <infAdic>
            <infCpl>NF-e emitida em ambiente de homologacao - sem valor fiscal</infCpl>
        </infAdic>
    </infNFe>
    </NFe>
    XML;

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
     * Monta os produtos da venda
     */
    private function getProdutos(Venda $venda): string
    {
        $produtosXml = '';
        $itens = $venda->itens; // Assumindo relação com itens da venda
        
        foreach ($itens as $index => $item) {
            $nItem = $index + 1;
            $valorPIS = number_format($item->preco_total * 0.0165, 2, '.', '');
            $valorCOFINS = number_format($item->preco_total * 0.0760, 2, '.', '');
            $qCom = number_format($item->quantidade, 4, '.', '');

            $preco_unitario = $item->preco_unitario - $item->desconto;

            $produtosXml .= <<<XML
    <det nItem="{$nItem}">
    <prod>
        <cProd>{$item->produto->codigo}</cProd>
        <cEAN>7890000000000</cEAN>
        <xProd>{$item->produto->nome_titulo}</xProd>
        <NCM>49019900</NCM>
        <CEST>2800300</CEST>
        <CFOP>5102</CFOP>
        <uCom>UN</uCom>
        <qCom>{$qCom}</qCom>
        <vUnCom>{$item->subtotal}</vUnCom>
        <vProd>{$item->preco_total}</vProd>
        <cEANTrib>7890000000000</cEANTrib>
        <uTrib>UN</uTrib>
        <qTrib>{$qCom}</qTrib>
        <vUnTrib>{$item->subtotal}</vUnTrib>
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
            <vBC>{$item->preco_total}</vBC>
            <pPIS>1.65</pPIS>
            <vPIS>{$valorPIS}</vPIS>
        </PISAliq>
        </PIS>
        <COFINS>
        <COFINSAliq>
            <CST>01</CST>
            <vBC>{$item->preco_total}</vBC>
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
     * Calcula totais da venda
     */
    private function getTotal(Venda $venda): array
    {
        $valorProdutos = $venda->valor_total;
        $valorPIS = $valorProdutos * 0.0165;
        $valorCOFINS = $valorProdutos * 0.0760;

        return [
            'valor_produtos' => number_format($valorProdutos, 2, '.', ''),
            'valor_pis' => number_format($valorPIS, 2, '.', ''),
            'valor_cofins' => number_format($valorCOFINS, 2, '.', ''),
            'valor_total' => number_format($valorProdutos, 2, '.', '')
        ];
    }

    private function gerarPagamento(Venda $venda)
    {
        $formaPagamento = $this->mapearFormaPagamentoNFe($venda->forma_pagamento);

        // Define se o pagamento é à vista (0) ou a prazo (1)
        $indPag = ($formaPagamento == '03' && $venda->quantidade_parcelas > 1) ? '1' : '0';

        return [
            'detPag' => [[
                'indPag' => $indPag,
                'tPag'   => $formaPagamento,
                'vPag'   => number_format($venda->valor_total, 2, '.', '')
            ]]
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
            "tpAmb" => 2,
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
        
        $tools = new Tools(json_encode($config), $certificate);
        
        // Assinar o XML
        return $tools->signNFe($xml);
    }


}