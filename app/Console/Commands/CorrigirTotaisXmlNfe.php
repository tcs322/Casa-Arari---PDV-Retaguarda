<?php

namespace App\Console\Commands;

use App\Models\Venda;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CorrigirTotaisXmlNfe extends Command
{
    protected $signature = 'nfe:corrigir-totais-xml {vendaId}';

    protected $description =
        'Corrige os totais de PIS e COFINS no xml_nfe de uma venda';

    public function handle(): int
    {
        $vendaId = $this->argument('vendaId');

        $venda = Venda::find($vendaId);

        if (!$venda) {
            $this->error(
                "Venda {$vendaId} não encontrada."
            );

            return self::FAILURE;
        }

        if (empty($venda->xml_nfe)) {
            $this->error(
                "A venda {$venda->id} não possui xml_nfe."
            );

            return self::FAILURE;
        }

        $this->info(
            "Venda encontrada: {$venda->id}"
        );

        $this->info(
            "NF: {$venda->numero_nota_fiscal}"
        );

        try {

            $this->corrigirTotaisPisCofinsNoXml($venda);

            $this->info(
                '✅ Totais de PIS e COFINS corrigidos com sucesso.'
            );

            return self::SUCCESS;

        } catch (\Throwable $e) {

            Log::error(
                'Erro ao corrigir totais de PIS e COFINS do XML.',
                [
                    'venda_id' => $venda->id,
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            $this->error(
                '❌ Erro: ' . $e->getMessage()
            );

            return self::FAILURE;
        }
    }

    private function corrigirTotaisPisCofinsNoXml(Venda $venda): void
    {
        if (empty($venda->xml_nfe)) {
            throw new \RuntimeException(
                "A venda {$venda->uuid} não possui xml_nfe armazenado."
            );
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        libxml_use_internal_errors(true);

        if (!$dom->loadXML($venda->xml_nfe)) {
            $erros = libxml_get_errors();
            libxml_clear_errors();

            $mensagens = array_map(
                fn ($erro) => trim($erro->message),
                $erros
            );

            throw new \RuntimeException(
                'Não foi possível carregar o XML da venda: ' .
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

        /*
        * =========================================================
        * 1. LOCALIZAR OS ITENS
        * =========================================================
        */
        $detNodes = $xpath->query(
            '//nfe:infNFe/nfe:det'
        );

        if ($detNodes->length === 0) {
            throw new \RuntimeException(
                "Nenhum item <det> foi encontrado no XML da venda {$venda->id}."
            );
        }

        /*
        * =========================================================
        * 2. SOMAR PIS E COFINS DOS ITENS
        * =========================================================
        *
        * Importante:
        *
        * Não recalculamos os tributos.
        *
        * Usamos exatamente os valores que já estão
        * registrados nos <vPIS> e <vCOFINS> dos itens.
        *
        * Dessa forma, o totalizador passa a ser exatamente
        * a soma dos valores que a SEFAZ utilizará na regra
        * de validação.
        */
        $totalPis = 0.0;
        $totalCofins = 0.0;

        foreach ($detNodes as $det) {

            $vPisNode = $xpath->query(
                './/nfe:PIS//nfe:vPIS',
                $det
            )->item(0);

            if ($vPisNode) {
                $totalPis += (float) $vPisNode->nodeValue;
            }

            $vCofinsNode = $xpath->query(
                './/nfe:COFINS//nfe:vCOFINS',
                $det
            )->item(0);

            if ($vCofinsNode) {
                $totalCofins += (float) $vCofinsNode->nodeValue;
            }
        }

        /*
        * =========================================================
        * 3. ARREDONDAR OS TOTAIS
        * =========================================================
        *
        * Os valores fiscais do XML possuem duas casas decimais.
        */
        $totalPis = round($totalPis, 2);
        $totalCofins = round($totalCofins, 2);

        $novoTotalPis = number_format(
            $totalPis,
            2,
            '.',
            ''
        );

        $novoTotalCofins = number_format(
            $totalCofins,
            2,
            '.',
            ''
        );

        /*
        * =========================================================
        * 4. LOCALIZAR ICMSTot
        * =========================================================
        */
        $icmsTotNodes = $xpath->query(
            '//nfe:infNFe/nfe:total/nfe:ICMSTot'
        );

        if ($icmsTotNodes->length !== 1) {
            throw new \RuntimeException(
                'Não foi possível localizar exatamente um <ICMSTot>.'
            );
        }

        $icmsTot = $icmsTotNodes->item(0);

        /*
        * =========================================================
        * 5. LOCALIZAR TOTALIZADORES ATUAIS
        * =========================================================
        */
        $vPisTotalNode = $xpath->query(
            './nfe:vPIS',
            $icmsTot
        )->item(0);

        $vCofinsTotalNode = $xpath->query(
            './nfe:vCOFINS',
            $icmsTot
        )->item(0);

        if (!$vPisTotalNode) {
            throw new \RuntimeException(
                'Não foi possível localizar <vPIS> dentro de <ICMSTot>.'
            );
        }

        if (!$vCofinsTotalNode) {
            throw new \RuntimeException(
                'Não foi possível localizar <vCOFINS> dentro de <ICMSTot>.'
            );
        }

        /*
        * =========================================================
        * 6. GUARDAR VALORES ANTIGOS
        * =========================================================
        */
        $pisAntigo = number_format(
            (float) $vPisTotalNode->nodeValue,
            2,
            '.',
            ''
        );

        $cofinsAntigo = number_format(
            (float) $vCofinsTotalNode->nodeValue,
            2,
            '.',
            ''
        );

        /*
        * =========================================================
        * 7. ATUALIZAR TOTALIZADORES
        * =========================================================
        */
        $vPisTotalNode->nodeValue = $novoTotalPis;
        $vCofinsTotalNode->nodeValue = $novoTotalCofins;

        /*
        * =========================================================
        * 8. SERIALIZAR XML
        * =========================================================
        */
        $xmlCorrigido = $dom->saveXML();

        if (!$xmlCorrigido) {
            throw new \RuntimeException(
                'Falha ao serializar o XML corrigido.'
            );
        }

        /*
        * =========================================================
        * 9. SALVAR NO BANCO
        * =========================================================
        */
        $venda->xml_nfe = $xmlCorrigido;
        $venda->save();

        /*
        * =========================================================
        * 10. LOG
        * =========================================================
        */
        Log::info(
            'Totais de PIS e COFINS corrigidos no XML histórico.',
            [
                'venda_id' => $venda->id,
                'venda_uuid' => $venda->uuid,
                'numero_nota' => $venda->numero_nota_fiscal,

                'pis_anterior' => $pisAntigo,
                'pis_novo' => $novoTotalPis,

                'cofins_anterior' => $cofinsAntigo,
                'cofins_novo' => $novoTotalCofins,

                'quantidade_itens' => $detNodes->length,
            ]
        );
    }
}