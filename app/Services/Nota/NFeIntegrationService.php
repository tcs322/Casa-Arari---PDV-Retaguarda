<?php
// app/Services/NFeIntegrationService.php

namespace App\Services\Nota;

use App\Models\Venda;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use DomDocument;

class NFeIntegrationService
{
    private $config;

    public function __construct()
    {
        $this->config = [
            'ambiente' => config('nfe.ambiente', 2), // 2-Homologação
            'versao' => '4.00',
            'codigo_uf' => '15', // Pará
            'modelo' => '55',
        ];
    }

    /**
     * Gera e envia NF-e para uma venda
     */
    public function emitirNFe(Venda $venda)
    {
        try {
            // 1. Gerar XML da NF-e
            $xmlData = $this->gerarXmlNFe($venda);
            
            // 2. Assinar XML (implementar posteriormente)
            $xmlAssinado = $this->assinarXml($xmlData['xml']);
            
            // 3. Enviar para SEFAZ (ambiente de homologação)
            $resposta = $this->enviarParaSefaz($xmlAssinado);
            
            // 4. Processar resposta
            if ($resposta['success']) {
                return [
                    'success' => true,
                    'numero_nota' => $resposta['numero'],
                    'chave_acesso' => $resposta['chave'],
                    'xml' => $resposta['xml'],
                    'mensagem' => 'NF-e autorizada com sucesso'
                ];
            } else {
                throw new \Exception($resposta['erro']);
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'erro' => $e->getMessage(),
                'mensagem' => 'Falha na emissão da NF-e'
            ];
        }
    }

    /**
     * Gera XML da NF-e baseado nos dados da venda
     */
    private function gerarXmlNFe(Venda $venda)
    {
        $itens = $venda->itens;
        $cliente = null; // Implementar conforme seu modelo
        
        $nfeData = [
            'ide' => $this->gerarDadosIde(),
            'emit' => $this->gerarDadosEmitente(),
            'dest' => $this->gerarDadosDestinatario($cliente),
            'det' => $this->gerarItens($itens),
            'total' => $this->gerarTotais($venda, $itens),
            'pag' => $this->gerarPagamento($venda),
            'infAdic' => $this->gerarInformacoesAdicionais($venda)
        ];

        $xml = $this->gerarXml($nfeData);
        $chave = $this->gerarChaveAcesso();

        return [
            'xml' => $xml,
            'chave' => $chave,
            'numero' => $nfeData['ide']['nNF']
        ];
    }

    /**
     * Gera dados do IDE (Identificação da NF-e)
     */
    private function gerarDadosIde()
    {
        return [
            'cUF' => $this->config['codigo_uf'],
            'cNF' => $this->gerarCodigoNumerico(),
            'natOp' => 'Venda de mercadoria',
            'mod' => $this->config['modelo'],
            'serie' => '1',
            'nNF' => $this->proximoNumeroNota(),
            'dhEmi' => now()->format('c'),
            'tpNF' => '1', // 1-Saída
            'idDest' => '1', // 1-Operação interna
            'cMunFG' => '1501402', // Belém/PA
            'tpImp' => '1', // DANFE Retrato
            'tpEmis' => '1', // Normal
            'cDV' => '1',
            'tpAmb' => $this->config['ambiente'],
            'finNFe' => '1', // Normal
            'indFinal' => '1', // Consumidor final
            'indPres' => '1', // Operação presencial
            'procEmi' => '0', // Aplicativo do contribuinte
            'verProc' => '1.0'
        ];
    }

    /**
     * Gera dados do emitente (sua livraria)
     */
    private function gerarDadosEmitente()
    {
        return [
            'CNPJ' => config('nfe.emitente_cnpj'),
            'xNome' => config('nfe.emitente_razao_social'),
            'xFant' => config('nfe.emitente_nome_fantasia'),
            'enderEmit' => [
                'xLgr' => config('nfe.emitente_logradouro'),
                'nro' => config('nfe.emitente_numero'),
                'xBairro' => config('nfe.emitente_bairro'),
                'cMun' => config('nfe.emitente_codigo_municipio'),
                'xMun' => config('nfe.emitente_municipio'),
                'UF' => config('nfe.emitente_uf'),
                'CEP' => config('nfe.emitente_cep'),
                'cPais' => '1058',
                'xPais' => 'BRASIL',
                'fone' => config('nfe.emitente_telefone')
            ],
            'IE' => config('nfe.emitente_ie'),
            'CRT' => config('nfe.emitente_crt', '1') // 1-Simples Nacional
        ];
    }

    /**
     * Gera dados do destinatário
     */
    private function gerarDadosDestinatario($cliente = null)
    {
        // Se não tem cliente, é consumidor final
        if (!$cliente) {
            return [
                'CPF' => '99999999999', // CPF genérico para consumo
                'xNome' => 'CONSUMIDOR FINAL',
                'indIEDest' => '9', // Não contribuinte
                'enderDest' => [
                    'xLgr' => 'Não informado',
                    'nro' => '0',
                    'xBairro' => 'Não informado',
                    'cMun' => '1501402', // Belém/PA
                    'xMun' => 'BELEM',
                    'UF' => 'PA',
                    'CEP' => '66000000',
                    'cPais' => '1058',
                    'xPais' => 'BRASIL'
                ]
            ];
        }

        // Implementar lógica para cliente cadastrado
        return [
            // ... dados do cliente cadastrado
        ];
    }

    /**
     * Gera itens da NF-e baseado nos itens da venda
     */
    private function gerarItens($itensVenda)
    {
        $itensNFe = [];
        
        foreach ($itensVenda as $index => $item) {
            $produto = Product::where('uuid', $item->produto_uuid)->first();
            
            if (!$produto) continue;

            $itemNFe = [
                '@attributes' => ['nItem' => $index + 1],
                'prod' => [
                    'cProd' => $produto->codigo ?? $produto->uuid,
                    'cEAN' => '7890000000000', // EAN genérico
                    'xProd' => $produto->nome,
                    'NCM' => $produto->ncm ?? $this->obterNcmPorCategoria($produto->categoria),
                    'CEST' => $produto->cest ?? $this->obterCestPorCategoria($produto->categoria),
                    'CFOP' => '5102',
                    'uCom' => 'UN',
                    'qCom' => number_format($item->quantidade, 4, '.', ''),
                    'vUnCom' => number_format($item->preco_unitario, 2, '.', ''),
                    'vProd' => number_format($item->subtotal, 2, '.', ''),
                    'cEANTrib' => '7890000000000',
                    'uTrib' => 'UN',
                    'qTrib' => number_format($item->quantidade, 4, '.', ''),
                    'vUnTrib' => number_format($item->preco_unitario, 2, '.', ''),
                    'indTot' => '1'
                ],
                'imposto' => $this->gerarImpostosItem($produto, $item)
            ];

            $itensNFe[] = $itemNFe;
        }

        return $itensNFe;
    }

    /**
     * Gera impostos para um item
     */
    private function gerarImpostosItem($produto, $item)
    {
        $valorTotal = $item->subtotal;
        $categoria = strtolower($produto->categoria ?? '');
        
        // Livros são isentos, outros produtos têm ICMS normal
        $isLivro = str_contains($categoria, 'livro') || 
                   str_contains($categoria, 'literatura') ||
                   ($produto->ncm ?? '') === '49019900';

        $aliquotaIcms = $isLivro ? 0 : 17.0;
        $valorIcms = ($valorTotal * $aliquotaIcms) / 100;

        return [
            'ICMS' => [
                'ICMS00' => [
                    'orig' => '0',
                    'CST' => '00',
                    'modBC' => '3',
                    'vBC' => number_format($valorTotal, 2, '.', ''),
                    'pICMS' => number_format($aliquotaIcms, 2, '.', ''),
                    'vICMS' => number_format($valorIcms, 2, '.', '')
                ]
            ],
            'PIS' => [
                'PISAliq' => [
                    'CST' => $isLivro ? '07' : '01',
                    'vBC' => number_format($valorTotal, 2, '.', ''),
                    'pPIS' => $isLivro ? '0' : '1.65',
                    'vPIS' => number_format($isLivro ? 0 : ($valorTotal * 0.0165), 2, '.', '')
                ]
            ],
            'COFINS' => [
                'COFINSAliq' => [
                    'CST' => $isLivro ? '07' : '01',
                    'vBC' => number_format($valorTotal, 2, '.', ''),
                    'pCOFINS' => $isLivro ? '0' : '7.6',
                    'vCOFINS' => number_format($isLivro ? 0 : ($valorTotal * 0.076), 2, '.', '')
                ]
            ]
        ];
    }

    /**
     * Gera totais da NF-e
     */
    private function gerarTotais(Venda $venda, $itens)
    {
        $vBC = 0;
        $vICMS = 0;
        $vProd = $venda->valor_total;

        foreach ($itens as $item) {
            $vBC += $item->subtotal;
            // Cálculo simplificado do ICMS
            $vICMS += ($item->subtotal * 17) / 100; // Média 17%
        }

        return [
            'ICMSTot' => [
                'vBC' => number_format($vBC, 2, '.', ''),
                'vICMS' => number_format($vICMS, 2, '.', ''),
                'vICMSDeson' => '0.00',
                'vFCP' => '0.00',
                'vBCST' => '0.00',
                'vST' => '0.00',
                'vFCPST' => '0.00',
                'vFCPSTRet' => '0.00',
                'vProd' => number_format($vProd, 2, '.', ''),
                'vFrete' => '0.00',
                'vSeg' => '0.00',
                'vDesc' => '0.00',
                'vII' => '0.00',
                'vIPI' => '0.00',
                'vIPIDevol' => '0.00',
                'vPIS' => number_format($vProd * 0.0165, 2, '.', ''),
                'vCOFINS' => number_format($vProd * 0.076, 2, '.', ''),
                'vOutro' => '0.00',
                'vNF' => number_format($vProd, 2, '.', ''),
                'vTotTrib' => '0.00'
            ]
        ];
    }

    /**
     * Gera dados de pagamento
     */
    private function gerarPagamento(Venda $venda)
    {
        $formaPagamento = $this->mapearFormaPagamentoNFe($venda->forma_pagamento);

        return [
            'detPag' => [
                [
                    'indPag' => '0', // 0-Pagamento à vista
                    'tPag' => $formaPagamento,
                    'vPag' => number_format($venda->valor_total, 2, '.', '')
                ]
            ]
        ];
    }

    /**
     * Mapeia forma de pagamento para código da NF-e
     */
    private function mapearFormaPagamentoNFe($formaPagamento)
    {
        $mapeamento = [
            'dinheiro' => '01',
            'cartao_credito' => '03',
            'cartao_debito' => '04',
            'pix' => '15'
        ];

        return $mapeamento[$formaPagamento] ?? '99'; // 99-Outros
    }

    /**
     * Gera informações adicionais
     */
    private function gerarInformacoesAdicionais(Venda $venda)
    {
        $info = "Venda realizada via PDV Livraria/Cafeteria\n";
        $info .= "Nº Venda: {$venda->uuid}\n";
        
        if ($venda->observacoes) {
            $info .= "Obs: {$venda->observacoes}\n";
        }

        return [
            'infCpl' => $info
        ];
    }

    /**
     * Gera XML final (implementação simplificada)
     */
    private function gerarXml($nfeData)
    {
        // Implementação básica - expandir conforme necessário
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        $nfeElement = $xml->createElement('NFe');
        $nfeElement->setAttribute('xmlns', 'http://www.portalfiscal.inf.br/nfe');
        
        $infNFe = $xml->createElement('infNFe');
        $infNFe->setAttribute('versao', '4.00');
        $infNFe->setAttribute('Id', 'NFe' . $this->gerarChaveAcesso());
        
        // Adicionar elementos ao XML...
        
        $nfeElement->appendChild($infNFe);
        $xml->appendChild($nfeElement);
        
        return $xml->saveXML();
    }

    /**
     * Gera chave de acesso
     */
    private function gerarChaveAcesso()
    {
        // Implementação simplificada
        return '15' . date('y') . date('m') . config('nfe.emitente_cnpj') . '55' . 
               str_pad($this->proximoNumeroNota(), 9, '0', STR_PAD_LEFT) . '100000000';
    }

    private function gerarCodigoNumerico()
    {
        return rand(10000000, 99999999);
    }

    private function proximoNumeroNota()
    {
        // Buscar último número usado no banco
        $ultimaNFe = Venda::whereNotNull('numero_nota_fiscal')
                          ->orderBy('created_at', 'desc')
                          ->first();
        
        return $ultimaNFe ? intval(substr($ultimaNFe->numero_nota_fiscal, -8)) + 1 : 1;
    }

    private function assinarXml($xml)
    {
        // TODO: Implementar assinatura digital
        return $xml;
    }

    private function enviarParaSefaz($xml)
    {
        // TODO: Implementar envio real para SEFAZ
        // Por enquanto, simula sucesso
        return [
            'success' => true,
            'numero' => $this->proximoNumeroNota(),
            'chave' => $this->gerarChaveAcesso(),
            'xml' => $xml,
            'protocolo' => '123456789012345'
        ];
    }

    private function obterNcmPorCategoria($categoria)
    {
        $mapeamento = [
            'livro' => '49019900',
            'cafe' => '09012100',
            'alimento' => '19059000',
            'bebida' => '22021000'
        ];

        $categoriaLower = strtolower($categoria);
        foreach ($mapeamento as $key => $ncm) {
            if (str_contains($categoriaLower, $key)) {
                return $ncm;
            }
        }

        return '49019900'; // Default para livro
    }

    private function obterCestPorCategoria($categoria)
    {
        $mapeamento = [
            'livro' => '2800300',
            'cafe' => '0300800',
            'alimento' => '0400300',
            'bebida' => '1100100'
        ];

        $categoriaLower = strtolower($categoria);
        foreach ($mapeamento as $key => $cest) {
            if (str_contains($categoriaLower, $key)) {
                return $cest;
            }
        }

        return '2800300'; // Default para livro
    }
}