<?php

namespace App\Console\Commands;

use App\Models\Venda;
use App\Services\Nota\NFeGenerateService;
use App\Services\Nota\SefaApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CompararXmlNFeHistorico extends Command
{
    protected $signature = 'nfe:comparar-historico {venda : ID ou UUID da venda}';

    protected $description = 'Gera o XML histórico de uma venda e compara com o XML atualmente armazenado, sem alterar o banco';

    public function handle(NFeGenerateService $nfeGenerateService): int
    {
        $identificador = $this->argument('venda');

        $this->info("🔍 Procurando venda: {$identificador}");

        // =========================================================
        // 1. BUSCAR VENDA
        // =========================================================

        $venda = Venda::find($identificador);

        if (!$venda) {
            $venda = Venda::where('uuid', $identificador)->first();
        }

        if (!$venda) {
            $this->error("❌ Venda não encontrada: {$identificador}");

            return self::FAILURE;
        }

        $this->newLine();

        $this->info('Venda encontrada:');

        $this->line("UUID: {$venda->uuid}");
        $this->line("ID: {$venda->id}");
        $this->line("Número NF-e: {$venda->numero_nota_fiscal}");
        $this->line("Série: {$venda->serie_nfe}");

        $this->line(
            "Data da venda: " .
            optional($venda->data_venda)->format('Y-m-d H:i:s')
        );

        // =========================================================
        // 2. XML ORIGINAL
        // =========================================================

        $this->newLine();

        if (empty($venda->xml_nfe)) {
            $this->error(
                '❌ A venda não possui XML armazenado em xml_nfe.'
            );

            return self::FAILURE;
        }

        $xmlOriginal = $venda->xml_nfe;

        $this->info('📄 XML original encontrado no banco.');

        // =========================================================
        // 3. RECONSTRUIR XML PARA REGULARIZAÇÃO
        // =========================================================

        try {
            $this->newLine();
        
            $this->info('🔄 Gerando novo XML histórico...');
        
            $xmlRegularizacao = $nfeGenerateService
                ->reconstruirXmlParaRegularizacao($venda);
        
            $this->info('✅ XML histórico gerado com sucesso.');
        
            $this->newLine();
        
            $this->info('🔐 Assinando XML para regularização...');
        
            $xmlRegularizacaoAssinado = $nfeGenerateService
                ->assinarXmlHistorico($xmlRegularizacao);
        
            $this->info('✅ XML histórico assinado com sucesso.');
        
        } catch (\Throwable $e) {
            $this->error('❌ Erro ao gerar ou assinar XML para regularização:');
            $this->error($e->getMessage());
        
            Log::error(
                'Erro ao gerar ou assinar XML para regularização no command.',
                [
                    'venda_id' => $venda->id,
                    'venda_uuid' => $venda->uuid,
                    'exception' => $e,
                ]
            );
        
            return self::FAILURE;
        }

        // =========================================================
        // 4. TESTAR PRODUTOS NO XML
        // =========================================================

        $this->newLine();

        $this->info('🧪 Testando produtos no XML para regularização...');

        $quantidadeDet = $this->contarElementosXml(
            $xmlRegularizacao,
            'det'
        );

        $this->line(
            "Itens encontrados no XML para regularização: {$quantidadeDet}"
        );

        // =========================================================
        // 5. SALVAR XMLs
        // =========================================================

        $nomeBase =
            'comparacao_nfe_' .
            $venda->numero_nota_fiscal .
            '_' .
            $venda->serie_nfe;

        $caminhoOriginal =
            storage_path("logs/{$nomeBase}_original.xml");

        $caminhoRegularizacao =
            storage_path("logs/{$nomeBase}_regularizacao.xml");
        
        $caminhoRegularizacaoAssinado =
            storage_path("logs/{$nomeBase}_regularizacao_assinado.xml");
        

        file_put_contents(
            $caminhoOriginal,
            $xmlOriginal
        );

        file_put_contents(
            $caminhoRegularizacao,
            $xmlRegularizacao
        );

        file_put_contents(
            $caminhoRegularizacaoAssinado,
            $xmlRegularizacaoAssinado
        );

        $this->newLine();

        $this->info('💾 XMLs salvos para comparação:');

        $this->line($caminhoOriginal);
        $this->line($caminhoRegularizacao);
        $this->line($caminhoRegularizacaoAssinado);

        // =========================================================
        // 6. ENVIO PARA SEFAZ
        // =========================================================

        $this->newLine();

        $this->info('📡 Enviando XML histórico assinado para a SEFAZ...');

        try {

            $sefazService = new SefaApiService();

            $resultado = $sefazService->autorizarNFe(
                $xmlRegularizacaoAssinado
            );

            // =====================================================
            // DEBUG TEMPORÁRIO
            // =====================================================

            $this->newLine();

            $this->info('🔬 RETORNO COMPLETO DO autorizarNFe():');

            $this->line(
                print_r($resultado, true)
            );

            // =====================================================
            // INFORMAÇÕES DE ERRO
            // =====================================================

            if (!empty($resultado['erro'])) {

                $this->warn('⚠️ Erro retornado pelo autorizarNFe():');

                $this->line(
                    $resultado['erro']
                );
            }

            Log::info(
                'Retorno completo do autorizarNFe() no reenvio histórico.',
                [
                    'venda_id' => $venda->id,
                    'venda_uuid' => $venda->uuid,
                    'resultado' => $resultado,
                ]
            );

            // =====================================================
            // NF-e AUTORIZADA
            // =====================================================

            if (($resultado['success'] ?? false) === true) {

                $this->info(
                    '✅ NF-e autorizada pela SEFAZ.'
                );

                if (!empty($resultado['chave_acesso'])) { 
                    
                    $venda->chave_acesso_nfe = $resultado['chave_acesso']; 
                    
                    $this->line( 
                        'Chave de acesso: ' . 
                        $resultado['chave_acesso'] 
                    ); 
                }

                if (!empty($resultado['numero_protocolo'])) { 
                    
                    $venda->protocolo_nfe = $resultado['numero_protocolo']; 
                    
                    $this->line( 
                        'Protocolo: ' . 
                        $resultado['numero_protocolo'] 
                    ); 
                }

                $venda->data_autorizacao_nfe = now();

                $venda->status = 'finalizada'; 
                $venda->status_nfe = 'autorizada';

                // -------------------------------------------------
                // XML reconstruído utilizado na autorização
                // -------------------------------------------------

                $venda->xml_nfe = $xmlRegularizacao;

                $this->info(
                    '💾 XML reconstruído salvo em xml_nfe.'
                );

                // -------------------------------------------------
                // XML autorizado retornado pela SEFAZ
                // -------------------------------------------------
                
                
                if (!empty($resultado['xml'])) {

                    $venda->xml_autorizado = $resultado['xml'];
                    
                    $this->info(
                        '💾 XML autorizado salvo em xml_autorizado.'
                    );

                } else {

                    $this->warn(
                        '⚠️ A SEFAZ autorizou a NF-e, mas nenhum XML foi retornado para gravação.'
                    );
                }

                // ------------------------------------------------- 
                // SALVAR DADOS DA AUTORIZAÇÃO 
                // ------------------------------------------------- 
                
                $venda->save(); 
                
                $this->info( 
                    '💾 Dados da autorização salvos na venda.' 
                );

                // -------------------------------------------------
                // MENSAGEM
                // -------------------------------------------------

                if (!empty($resultado['mensagem'])) {

                    $this->line(
                        'Mensagem: ' .
                        $resultado['mensagem']
                    );
                }

            } else {

                // =================================================
                // NF-e NÃO AUTORIZADA
                // =================================================

                $this->error(
                    '❌ NF-e não foi autorizada pela SEFAZ.'
                );

                $this->line(
                    'Tipo: ' .
                    ($resultado['tipo'] ?? 'desconhecido')
                );

                $this->line(
                    'Código: ' .
                    ($resultado['codigo_erro'] ?? 'não informado')
                );

                $this->line(
                    'Mensagem: ' .
                    ($resultado['mensagem'] ?? 'não informada')
                );

                if (!empty($resultado['erro'])) {

                    $this->line(
                        'Erro: ' .
                        $resultado['erro']
                    );
                }

                $this->warn(
                    '⚠️ O XML existente no banco NÃO foi alterado.'
                );
            }

        } catch (\Throwable $e) {

            // =====================================================
            // ERRO DURANTE O ENVIO
            // =====================================================

            $this->error(
                '❌ Erro inesperado durante o envio para a SEFAZ.'
            );

            $this->error(
                $e->getMessage()
            );

            Log::error(
                'Erro inesperado ao reenviar NF-e histórica.',
                [
                    'venda_id' => $venda->id,
                    'venda_uuid' => $venda->uuid,
                    'exception' => $e,
                ]
            );
        }


        // =========================================================
        // 7. COMPARAÇÃO
        // =========================================================

        $this->newLine();

        $this->info('🔎 Comparando XMLs...');

        $this->compararXmlsConsiderandoAlteracoesConhecidas(
            $xmlOriginal,
            $xmlRegularizacao
        );

        // =========================================================
        // 7. INFORMAÇÕES BÁSICAS
        // =========================================================

        $this->newLine();

        $this->line(
            'Tamanho XML original: ' .
            strlen($xmlOriginal) .
            ' bytes'
        );

        $this->line(
            'Tamanho XML histórico: ' .
            strlen($xmlRegularizacao) .
            ' bytes'
        );

        // =========================================================
        // 8. DATAS DE EMISSÃO
        // =========================================================

        $this->newLine();

        $this->info('📅 Datas de emissão:');

        $this->mostrarDataEmissao(
            $xmlOriginal,
            'Original'
        );

        $this->mostrarDataEmissao(
            $xmlRegularizacao,
            'Histórico'
        );

        // =========================================================
        // 9. CHAVE DE ACESSO
        // =========================================================

        $this->newLine();

        $this->info('🔑 Identificação da NF-e:');

        $this->mostrarIdentificacaoNFe(
            $xmlOriginal,
            'Original'
        );

        $this->mostrarIdentificacaoNFe(
            $xmlRegularizacao,
            'Regularização'
        );

        $this->newLine();

        $this->warn(
            'Os arquivos foram preservados em storage/logs/ para comparação detalhada.'
        );

        return self::SUCCESS;
    }

    /**
     * =============================================================
     * COMPARADOR PRINCIPAL
     * =============================================================
     *
     * Compara os XMLs removendo previamente as diferenças que
     * sabemos serem intencionais nesta etapa.
     */
    private function compararXmlsConsiderandoAlteracoesConhecidas(
        string $xmlOriginal,
        string $xmlHistorico
    ): void {
        $domOriginal = new \DOMDocument('1.0', 'UTF-8');
        $domHistorico = new \DOMDocument('1.0', 'UTF-8');

        $domOriginal->preserveWhiteSpace = false;
        $domHistorico->preserveWhiteSpace = false;

        libxml_use_internal_errors(true);

        if (!$domOriginal->loadXML($xmlOriginal)) {
            $this->error(
                '❌ Não foi possível interpretar o XML original.'
            );

            libxml_clear_errors();

            return;
        }

        if (!$domHistorico->loadXML($xmlHistorico)) {
            $this->error(
                '❌ Não foi possível interpretar o XML histórico.'
            );

            libxml_clear_errors();

            return;
        }

        libxml_clear_errors();

        // =========================================================
        // 1. CRIAR XPATHs
        // =========================================================

        $xpathOriginal = new \DOMXPath($domOriginal);
        $xpathHistorico = new \DOMXPath($domHistorico);

        $xpathOriginal->registerNamespace(
            'nfe',
            'http://www.portalfiscal.inf.br/nfe'
        );

        $xpathHistorico->registerNamespace(
            'nfe',
            'http://www.portalfiscal.inf.br/nfe'
        );

        $xpathOriginal->registerNamespace(
            'ds',
            'http://www.w3.org/2000/09/xmldsig#'
        );

        $xpathHistorico->registerNamespace(
            'ds',
            'http://www.w3.org/2000/09/xmldsig#'
        );

        // =========================================================
        // 2. REMOVER ASSINATURA
        // =========================================================

        foreach (
            $xpathOriginal->query('//ds:Signature') as $node
        ) {
            $node->parentNode->removeChild($node);
        }

        foreach (
            $xpathHistorico->query('//ds:Signature') as $node
        ) {
            $node->parentNode->removeChild($node);
        }

        // =========================================================
        // 3. REMOVER infAdic
        // =========================================================

        foreach (
            $xpathOriginal->query('//nfe:infNFe/nfe:infAdic') as $node
        ) {
            $node->parentNode->removeChild($node);
        }

        foreach (
            $xpathHistorico->query('//nfe:infNFe/nfe:infAdic') as $node
        ) {
            $node->parentNode->removeChild($node);
        }

        // =========================================================
        // 4. REMOVER TELEFONE
        // =========================================================

        foreach (
            $xpathOriginal->query(
                '//nfe:infNFe/nfe:emit/nfe:enderEmit/nfe:fone'
            ) as $node
        ) {
            $node->parentNode->removeChild($node);
        }

        foreach (
            $xpathHistorico->query(
                '//nfe:infNFe/nfe:emit/nfe:enderEmit/nfe:fone'
            ) as $node
        ) {
            $node->parentNode->removeChild($node);
        }

        // =========================================================
        // 5. NORMALIZAR tpImp
        // =========================================================

        foreach (
            $xpathOriginal->query(
                '//nfe:infNFe/nfe:ide/nfe:tpImp'
            ) as $node
        ) {
            $node->nodeValue = 'IGNORADO_TPIMP';
        }

        foreach (
            $xpathHistorico->query(
                '//nfe:infNFe/nfe:ide/nfe:tpImp'
            ) as $node
        ) {
            $node->nodeValue = 'IGNORADO_TPIMP';
        }

        // =========================================================
        // 6. REMOVER CARD
        // =========================================================

        foreach (
            $xpathOriginal->query(
                '//nfe:infNFe/nfe:pag/nfe:detPag/nfe:card'
            ) as $node
        ) {
            $node->parentNode->removeChild($node);
        }

        foreach (
            $xpathHistorico->query(
                '//nfe:infNFe/nfe:pag/nfe:detPag/nfe:card'
            ) as $node
        ) {
            $node->parentNode->removeChild($node);
        }

        // =========================================================
        // 7. COMPARAR
        // =========================================================

        $diferencas = $this->compararNosXml(
            $xpathOriginal,
            $xpathHistorico
        );

        // =========================================================
        // 8. RESULTADO
        // =========================================================

        if (empty($diferencas)) {
            $this->info(
                '✅ Nenhuma diferença fiscal inesperada encontrada.'
            );

            $this->newLine();

            $this->line(
                'Alterações conhecidas ignoradas:'
            );

            $this->line('  • tpImp');
            $this->line('  • fone');
            $this->line('  • card');
            $this->line('  • infAdic');
            $this->line('  • Signature');
            $this->line('  • dhEmi');
            $this->line('  • cDV');

            return;
        }

        $this->warn(
            '⚠️ Foram encontradas ' .
            count($diferencas) .
            ' diferença(s) inesperada(s):'
        );

        $this->newLine();

        foreach ($diferencas as $diferenca) {

            $this->line(
                "Tipo: {$diferenca['tipo']}"
            );

            $this->line(
                "Tag: {$diferenca['caminho']}"
            );

            $this->line(
                "Original: {$diferenca['original']}"
            );

            $this->line(
                "Histórico: {$diferenca['historico']}"
            );

            $this->newLine();
        }
    }

    /**
     * =============================================================
     * COMPARAÇÃO DOS NÓS
     * =============================================================
     */
    
    private function compararNosXml(
        \DOMXPath $xpathOriginal,
        \DOMXPath $xpathHistorico
    ): array {
        $diferencas = [];

        /*
        * =========================================================
        * EXCEÇÕES
        * =========================================================
        *
        * Estas tags podem sofrer alterações intencionais durante
        * a reconstrução do XML e, portanto, não devem ser
        * consideradas diferenças inesperadas.
        */
        $caminhosIgnorados = [
            '/NFe[1]/infNFe[1]/ide[1]/dhEmi[1]',
            '/NFe[1]/infNFe[1]/ide[1]/cDV[1]',
            '/NFe[1]/infNFeSupl[1]/qrCode[1]',
            '/NFe[1]/infNFeSupl[1]/urlChave[1]',
        ];

        /*
        * =========================================================
        * ELEMENTOS FOLHA DO XML ORIGINAL
        * =========================================================
        */
        $nosOriginais = $xpathOriginal->query(
            '//*[namespace-uri()="http://www.portalfiscal.inf.br/nfe" and not(*)]'
        );

        $nosHistoricos = $xpathHistorico->query(
            '//*[namespace-uri()="http://www.portalfiscal.inf.br/nfe" and not(*)]'
        );

        /*
        * =========================================================
        * INDEXAR XML HISTÓRICO
        * =========================================================
        */

        $mapaHistorico = [];

        foreach ($nosHistoricos as $nodeHistorico) {

            $caminho = $this->obterXPathRelativo(
                $nodeHistorico
            );

            $mapaHistorico[$caminho] = trim(
                $nodeHistorico->nodeValue
            );
        }

        /*
        * =========================================================
        * COMPARAR ORIGINAL -> HISTÓRICO
        * =========================================================
        */

        foreach ($nosOriginais as $nodeOriginal) {

            $caminho = $this->obterXPathRelativo(
                $nodeOriginal
            );

            /*
            * Esta diferença é esperada e deve ser ignorada.
            */
            if (in_array($caminho, $caminhosIgnorados, true)) {
                continue;
            }

            $valorOriginal = trim(
                $nodeOriginal->nodeValue
            );

            /*
            * Tag não existe no XML histórico.
            */
            if (!array_key_exists($caminho, $mapaHistorico)) {

                $diferencas[] = [
                    'tipo' => 'AUSENTE',
                    'caminho' => $caminho,
                    'original' => $valorOriginal,
                    'historico' => '[NÃO EXISTE]',
                ];

                continue;
            }

            $valorHistorico = $mapaHistorico[$caminho];

            /*
            * Tag existe nos dois XMLs, mas o valor mudou.
            */
            if ($valorOriginal !== $valorHistorico) {

                $diferencas[] = [
                    'tipo' => 'ALTERADO',
                    'caminho' => $caminho,
                    'original' => $valorOriginal,
                    'historico' => $valorHistorico,
                ];
            }
        }

        /*
        * =========================================================
        * INDEXAR XML ORIGINAL
        * =========================================================
        */

        $mapaOriginal = [];

        foreach ($nosOriginais as $nodeOriginal) {

            $caminho = $this->obterXPathRelativo(
                $nodeOriginal
            );

            $mapaOriginal[$caminho] = trim(
                $nodeOriginal->nodeValue
            );
        }

        /*
        * =========================================================
        * VERIFICAR TAGS NOVAS NO HISTÓRICO
        * =========================================================
        */

        foreach ($nosHistoricos as $nodeHistorico) {

            $caminho = $this->obterXPathRelativo(
                $nodeHistorico
            );

            /*
            * Esta diferença é esperada e deve ser ignorada.
            */
            if (in_array($caminho, $caminhosIgnorados, true)) {
                continue;
            }

            if (!array_key_exists($caminho, $mapaOriginal)) {

                $diferencas[] = [
                    'tipo' => 'NOVO',
                    'caminho' => $caminho,
                    'original' => '[NÃO EXISTIA]',
                    'historico' => trim(
                        $nodeHistorico->nodeValue
                    ),
                ];
            }
        }

        return $diferencas;
    }


    /**
     * =============================================================
     * CONSTRUIR CAMINHO XPATH
     * =============================================================
     */
    private function obterXPathRelativo(\DOMNode $node): string
    {
        $partes = [];
    
        while ($node instanceof \DOMElement) {
    
            /*
             * Usa localName em vez de nodeName.
             *
             * Assim:
             *
             * nfe:infNFe
             * infNFe
             *
             * serão tratados simplesmente como:
             *
             * infNFe
             */
            $nome = $node->localName;
    
            $index = 1;
    
            $irmao = $node->previousSibling;
    
            while ($irmao) {
    
                if (
                    $irmao instanceof \DOMElement
                    && $irmao->localName === $nome
                    && $irmao->namespaceURI === $node->namespaceURI
                ) {
                    $index++;
                }
    
                $irmao = $irmao->previousSibling;
            }
    
            $partes[] = $nome . '[' . $index . ']';
    
            $node = $node->parentNode;
        }
    
        return '/' . implode(
            '/',
            array_reverse($partes)
        );
    }

    /**
     * =============================================================
     * CONTAR ELEMENTOS
     * =============================================================
     */
    private function contarElementosXml(
        string $xml,
        string $elemento
    ): int {
        $dom = new \DOMDocument();

        if (!@$dom->loadXML($xml)) {
            return 0;
        }

        $xpath = new \DOMXPath($dom);

        return $xpath->evaluate(
            'count(//*[local-name()="' . $elemento . '"])'
        );
    }

    /**
     * =============================================================
     * DATA DE EMISSÃO
     * =============================================================
     */
    private function mostrarDataEmissao(
        string $xml,
        string $tipo
    ): void {
        $dom = new \DOMDocument();

        if (!@$dom->loadXML($xml)) {
            $this->line(
                "{$tipo}: XML inválido"
            );

            return;
        }

        $xpath = new \DOMXPath($dom);

        $nodes = $xpath->evaluate(
            '//*[local-name()="dhEmi"]'
        );

        if ($nodes->length > 0) {

            $this->line(
                "{$tipo}: {$nodes->item(0)->nodeValue}"
            );

        } else {

            $this->line(
                "{$tipo}: <dhEmi> não encontrada"
            );
        }
    }

    /**
     * =============================================================
     * IDENTIFICAÇÃO DA NF-e
     * =============================================================
     */
    private function mostrarIdentificacaoNFe(
        string $xml,
        string $tipo
    ): void {
        $dom = new \DOMDocument();

        if (!@$dom->loadXML($xml)) {
            $this->line(
                "{$tipo}: XML inválido"
            );

            return;
        }

        $xpath = new \DOMXPath($dom);

        $infNFe = $xpath->query(
            '//*[local-name()="infNFe"]'
        )->item(0);

        if (!$infNFe instanceof \DOMElement) {
            $this->line(
                "{$tipo}: <infNFe> não encontrada"
            );

            return;
        }

        $id = $infNFe->getAttribute('Id');

        $this->line(
            "{$tipo} - Id: {$id}"
        );

        $ide = $xpath->query(
            '//*[local-name()="ide"]'
        )->item(0);

        if (!$ide instanceof \DOMElement) {
            return;
        }

        $campos = [
            'cUF',
            'dhEmi',
            'mod',
            'serie',
            'nNF',
            'tpEmis',
            'cNF',
            'cDV',
        ];

        foreach ($campos as $campo) {

            $node = $xpath->query(
                './*[local-name()="' . $campo . '"]',
                $ide
            )->item(0);

            $valor = $node
                ? trim($node->nodeValue)
                : '[NÃO ENCONTRADO]';

            $this->line(
                "  {$campo}: {$valor}"
            );
        }
    }
}