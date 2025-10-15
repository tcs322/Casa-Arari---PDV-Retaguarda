<?php
// app/Services/Nota/NFeIntegrationService.php

namespace App\Services\Nota;

use App\Enums\FormaPagamentoEnum;
use App\Models\Venda;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMElement;

class NFeIntegrationService
{
    private $config;

    public function __construct()
    {
        $this->config = [
            'ambiente' => config('nfe.ambiente', 2),
            'versao' => '4.00',
            'codigo_uf' => '15',
            'modelo' => '55',
        ];
    }

    public function emitirNFe(Venda $venda)
    {
        try {
            Log::info('🔧 [MODO SIMULAÇÃO] Iniciando emissão SIMULADA de NF-e para venda: ' . $venda->uuid);

            // 1. Gerar XML da NF-e (VAMOS CORRIGIR ESTA PARTE)
            $xmlData = $this->gerarXmlNFe($venda);
            Log::info('📄 XML gerado para venda: ' . $venda->uuid);

            // 2. Simular assinatura
            $xmlAssinado = $this->assinarXmlSimulado($xmlData['xml']);
            
            // 3. Simular envio para SEFAZ
            $resultadoSimulado = $this->simularEnvioSefaz($xmlAssinado, $venda, $xmlData['chave']);
            
            Log::info('✅ [SIMULAÇÃO] NF-e simulada com sucesso para venda: ' . $venda->uuid);
            
            return $resultadoSimulado;

        } catch (\Exception $e) {
            Log::error('❌ Erro na simulação de NF-e para venda ' . $venda->uuid . ': ' . $e->getMessage());
            
            return [
                'success' => false,
                'erro' => $e->getMessage(),
                'mensagem' => 'Falha na simulação da NF-e'
            ];
        }
    }

    /**
     * GERA XML COMPLETO DA NF-E (MÉTODO CORRIGIDO)
     */
    private function gerarXmlNFe(Venda $venda)
    {
        $chave = $this->gerarChaveAcessoSimulada();
        
        $nfeData = [
            'infNFe' => [
                '@attributes' => [
                    'versao' => $this->config['versao'],
                    'Id' => 'NFe' . $chave
                ],
                'ide' => $this->gerarDadosIde(),
                'emit' => $this->gerarDadosEmitente(),
                'dest' => $this->gerarDadosDestinatario(),
                'det' => $this->gerarItens($venda),
                'total' => $this->gerarTotais($venda),
                'pag' => $this->gerarPagamento($venda),
                'infAdic' => $this->gerarInformacoesAdicionais($venda)
            ]
        ];

        $xml = $this->gerarXml($nfeData);

        return [
            'xml' => $xml,
            'chave' => $chave,
            'numero' => $nfeData['infNFe']['ide']['nNF']
        ];
    }

    /**
     * GERA DADOS DE IDENTIFICAÇÃO
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
            'dhEmi' => now()->format('Y-m-d\TH:i:sP'),
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
     * GERA DADOS DO EMITENTE
     */
    private function gerarDadosEmitente()
    {
        return [
            'CNPJ' => config('nfe.emitente_cnpj', '99999999000191'),
            'xNome' => config('nfe.emitente_razao_social', 'LIVRARIA CAFETERIA DO PARA LTDA'),
            'xFant' => config('nfe.emitente_nome_fantasia', 'Livraria & Café PA'),
            'enderEmit' => [
                'xLgr' => 'AVENIDA PRESIDENTE VARGAS',
                'nro' => '1000',
                'xBairro' => 'CENTRO',
                'cMun' => '1501402',
                'xMun' => 'BELEM',
                'UF' => 'PA',
                'CEP' => '66000000',
                'cPais' => '1058',
                'xPais' => 'BRASIL',
                'fone' => '9133334444'
            ],
            'IE' => config('nfe.emitente_ie', '999999999'),
            'CRT' => config('nfe.emitente_crt', '1')
        ];
    }

    /**
     * GERA DADOS DO DESTINATÁRIO (CONSUMIDOR FINAL)
     */
    private function gerarDadosDestinatario()
    {
        return [
            'CPF' => '99999999999',
            'xNome' => 'CONSUMIDOR FINAL',
            'indIEDest' => '9', // Não contribuinte
            'enderDest' => [
                'xLgr' => 'Não informado',
                'nro' => '0',
                'xBairro' => 'Não informado',
                'cMun' => '1501402',
                'xMun' => 'BELEM',
                'UF' => 'PA',
                'CEP' => '66000000',
                'cPais' => '1058',
                'xPais' => 'BRASIL'
            ]
        ];
    }

    /**
     * Gera itens baseados nos dados reais da venda
     */
    private function gerarItens(Venda $venda)
    {
        $itens = [];
        $contador = 1;
        
        // ✅ AGORA USANDO OS ITENS REAIS DA VENDA
        foreach ($venda->itens as $itemVenda) {
            $produto = Product::where('uuid', $itemVenda->produto_uuid)->first();
            
            if ($produto) {
                $itens[] = $this->gerarItemReal($itemVenda, $produto, $contador);
                $contador++;
            }
        }

        // Se não houver itens reais, usa os de teste (fallback)
        if (empty($itens)) {
            Log::warning('Nenhum item real encontrado para venda ' . $venda->uuid . ', usando dados de teste');
            return $this->gerarItensTeste($venda);
        }

        return $itens;
    }

    /**
     * Gera itens de teste (fallback)
     */
    private function gerarItensTeste(Venda $venda)
    {
        $itens = [];
        $itensTeste = [
            [
                'descricao' => 'LIVRO: DOM QUIXOTE',
                'quantidade' => 1,
                'valor_unitario' => $venda->valor_total * 0.7,
                'ncm' => '49019900',
                'cest' => '2800300'
            ],
            [
                'descricao' => 'CAFÉ EXPRESSO',
                'quantidade' => 1, 
                'valor_unitario' => $venda->valor_total * 0.3,
                'ncm' => '09012100',
                'cest' => '0300800'
            ]
        ];

        foreach ($itensTeste as $index => $item) {
            $itens[] = $this->gerarItem($item, $index + 1);
        }

        return $itens;
}

    /**
     * Gera item da NF-e baseado nos dados reais do carrinho
     */
    private function gerarItemReal($itemVenda, $produto, $numeroItem)
    {
        $valorTotal = $itemVenda->subtotal;
        $valorUnitario = $itemVenda->preco_unitario;
        $quantidade = $itemVenda->quantidade;

        return [
            '@attributes' => ['nItem' => $numeroItem],
            'prod' => [
                'cProd' => $produto->codigo,
                'cEAN' => $produto->codigo_barras ?? '7890000000000',
                'xProd' => $produto->nome_titulo,
                'NCM' => $produto->ncm,
                'CEST' => $produto->cest,
                'CFOP' => $produto->cfop,
                'uCom' => $produto->unidade_medida,
                'qCom' => number_format($quantidade, 4, '.', ''),
                'vUnCom' => number_format($valorUnitario, 2, '.', ''),
                'vProd' => number_format($valorTotal, 2, '.', ''),
                'cEANTrib' => $produto->codigo_barras ?? '7890000000000',
                'uTrib' => $produto->unidade_medida,
                'qTrib' => number_format($quantidade, 4, '.', ''),
                'vUnTrib' => number_format($valorUnitario, 2, '.', ''),
                'indTot' => '1'
            ],
            'imposto' => $this->gerarImpostosItemComCamposFiscais($produto, $valorTotal)
        ];
    }

    private function gerarImpostosItemComCamposFiscais($produto, $valorTotal)
    {
        $aliquotaIcms = $produto->aliquota_icms;
        $cstIcms = $produto->cst_icms;
        
        // Estrutura base do ICMS
        $icms = [
            'orig' => $produto->origem,
            'CST' => $cstIcms
        ];

        // Define a tag e campos específicos baseado no CST
        $tagIcms = 'ICMS00'; // default
        
        if ($cstIcms == '40' || $cstIcms == '41' || $cstIcms == '50') {
            // Isenta, não tributada ou suspensão
            $tagIcms = 'ICMS40';
            $icms['vICMS'] = number_format(0, 2, '.', '');
            // Não inclui modBC, vBC, pICMS para CST 40
        } else {
            // Tributada normal (CST 00, 10, 20, etc.)
            $tagIcms = 'ICMS00';
            $icms['modBC'] = '3';
            $icms['vBC'] = number_format($valorTotal, 2, '.', '');
            $icms['pICMS'] = number_format($aliquotaIcms, 2, '.', '');
            $icms['vICMS'] = number_format(($valorTotal * $aliquotaIcms) / 100, 2, '.', '');
        }

        return [
            'ICMS' => [
                $tagIcms => $icms
            ],
            'PIS' => [
                'PISAliq' => [
                    'CST' => $produto->cst_pis,
                    'vBC' => number_format($valorTotal, 2, '.', ''),
                    'pPIS' => $produto->cst_pis == '07' ? '0' : '1.65',
                    'vPIS' => number_format($produto->cst_pis == '07' ? 0 : ($valorTotal * 0.0165), 2, '.', '')
                ]
            ],
            'COFINS' => [
                'COFINSAliq' => [
                    'CST' => $produto->cst_cofins,
                    'vBC' => number_format($valorTotal, 2, '.', ''),
                    'pCOFINS' => $produto->cst_cofins == '07' ? '0' : '7.6',
                    'vCOFINS' => number_format($produto->cst_cofins == '07' ? 0 : ($valorTotal * 0.076), 2, '.', '')
                ]
            ]
        ];
    }

    /**
     * Gera impostos para item real
     */
    private function gerarImpostosItemReal($produto, $valorTotal, $isLivro)
    {
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
     * Verifica se o produto é um livro
     */
    private function isProdutoLivro($produto)
    {
        // Estratégia 1: Verificar NCM específico para livros
        if ($produto->ncm == '49019900') {
            return true;
        }
        
        // Estratégia 2: Verificar categoria
        $categoria = strtolower($produto->categoria ?? '');
        $palavrasChaveLivro = ['livro', 'literatura', 'revista', 'jornal', 'publicação'];
        
        foreach ($palavrasChaveLivro as $palavra) {
            if (str_contains($categoria, $palavra)) {
                return true;
            }
        }
        
        // Estratégia 3: Verificar nome do produto
        $nome = strtolower($produto->nome);
        foreach ($palavrasChaveLivro as $palavra) {
            if (str_contains($nome, $palavra)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtém NCM baseado na categoria (fallback)
     */
    private function obterNcmPorCategoria($categoria)
    {
        $mapeamento = [
            'livro' => '49019900',
            'cafe' => '09012100',
            'alimento' => '19059000',
            'bebida' => '22021000',
            'papelaria' => '48201000'
        ];

        $categoriaLower = strtolower($categoria ?? '');
        foreach ($mapeamento as $key => $ncm) {
            if (str_contains($categoriaLower, $key)) {
                return $ncm;
            }
        }

        return '49019900'; // Default para livro
    }

    /**
     * Obtém CEST baseado na categoria (fallback)
     */
    private function obterCestPorCategoria($categoria)
    {
        $mapeamento = [
            'livro' => '2800300',
            'cafe' => '0300800', 
            'alimento' => '0400300',
            'bebida' => '1100100',
            'papelaria' => '2805300'
        ];

        $categoriaLower = strtolower($categoria ?? '');
        foreach ($mapeamento as $key => $cest) {
            if (str_contains($categoriaLower, $key)) {
                return $cest;
            }
        }

        return '2800300'; // Default para livro
    }

    /**
     * GERA UM ITEM INDIVIDUAL
     */
    private function gerarItem($produto, $numeroItem)
    {
        $valorTotal = $produto['quantidade'] * $produto['valor_unitario'];
        $isLivro = $produto['ncm'] === '49019900';

        return [
            '@attributes' => ['nItem' => $numeroItem],
            'prod' => [
                'cProd' => 'PROD' . $numeroItem,
                'cEAN' => '7890000000000',
                'xProd' => $produto['descricao'],
                'NCM' => $produto['ncm'],
                'CEST' => $produto['cest'],
                'CFOP' => '5102',
                'uCom' => 'UN',
                'qCom' => number_format($produto['quantidade'], 4, '.', ''),
                'vUnCom' => number_format($produto['valor_unitario'], 2, '.', ''),
                'vProd' => number_format($valorTotal, 2, '.', ''),
                'cEANTrib' => '7890000000000',
                'uTrib' => 'UN',
                'qTrib' => number_format($produto['quantidade'], 4, '.', ''),
                'vUnTrib' => number_format($produto['valor_unitario'], 2, '.', ''),
                'indTot' => '1'
            ],
            'imposto' => $this->gerarImpostosItem($produto, $valorTotal)
        ];
    }

    /**
     * GERA IMPOSTOS DO ITEM
     */
    private function gerarImpostosItem($produto, $valorTotal)
    {
        $isLivro = $produto['ncm'] === '49019900';
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
     * GERA TOTAIS
     */
    private function gerarTotais(Venda $venda)
    {
        $vBC = 0;
        $vICMS = 0;
        $vProd = $venda->valor_total;
        $vPIS = 0;
        $vCOFINS = 0;

        // Calcular impostos baseados nos itens reais
        foreach ($venda->itens as $itemVenda) {
            $produto = Product::where('uuid', $itemVenda->produto_uuid)->first();
            
            if ($produto) {
                $valorItem = $itemVenda->subtotal;
                $vBC += $valorItem;
                
                // Só adiciona ICMS se não for isento
                if ($produto->aliquota_icms > 0) {
                    $vICMS += ($valorItem * $produto->aliquota_icms) / 100;
                }
                
                // Só adiciona PIS/COFINS se não for isento
                if ($produto->cst_pis != '07') {
                    $vPIS += $valorItem * 0.0165; // 1.65%
                }
                if ($produto->cst_cofins != '07') {
                    $vCOFINS += $valorItem * 0.076; // 7.6%
                }
            }
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
                'vPIS' => number_format($vPIS, 2, '.', ''),
                'vCOFINS' => number_format($vCOFINS, 2, '.', ''),
                'vOutro' => '0.00',
                'vNF' => number_format($vProd, 2, '.', ''),
                'vTotTrib' => '0.00'
            ]
        ];
    }

    /**
     * GERA PAGAMENTO
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
     * GERA INFORMAÇÕES ADICIONAIS
     */
    private function gerarInformacoesAdicionais(Venda $venda)
    {
        return [
            'infCpl' => "Venda realizada via PDV Livraria/Cafeteria\nNº Venda: {$venda->uuid}"
        ];
    }

    /**
     * GERA XML FINAL (MÉTODO CORRIGIDO)
     */
    private function gerarXml($nfeData)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        
        $nfeElement = $dom->createElement('NFe');
        $nfeElement->setAttribute('xmlns', 'http://www.portalfiscal.inf.br/nfe');
        
        $this->arrayParaXml($nfeData['infNFe'], $nfeElement, $dom);
        
        $dom->appendChild($nfeElement);
        
        return $dom->saveXML();
    }

    /**
     * CONVERTE ARRAY PARA XML
     */
    private function arrayParaXml($array, $elementoPai, $dom)
    {
        foreach ($array as $chave => $valor) {
            if ($chave === '@attributes') {
                foreach ($valor as $attr => $attrValor) {
                    $elementoPai->setAttribute($attr, $attrValor);
                }
            } else {
                if (is_numeric($chave)) {
                    $elementoFilho = $dom->createElement($elementoPai->tagName);
                } else {
                    $elementoFilho = $dom->createElement($chave);
                }
                
                if (is_array($valor)) {
                    $this->arrayParaXml($valor, $elementoFilho, $dom);
                } else {
                    $elementoFilho->nodeValue = htmlspecialchars($valor);
                }
                
                $elementoPai->appendChild($elementoFilho);
            }
        }
    }

    // ... MANTENHA OS OUTROS MÉTODOS (assinarXmlSimulado, simularEnvioSefaz, etc.)
    
    private function gerarCodigoNumerico()
    {
        return rand(10000000, 99999999);
    }

    private function proximoNumeroNota()
    {
        $ultimaNFe = Venda::whereNotNull('numero_nota_fiscal')
                          ->orderBy('created_at', 'desc')
                          ->first();
        
        return $ultimaNFe ? intval($ultimaNFe->numero_nota_fiscal) + 1 : 1;
    }

    private function assinarXmlSimulado($xml)
    {
        Log::info('🔏 [SIMULAÇÃO] Simulando assinatura digital');
        return $xml;
    }

    private function simularEnvioSefaz($xmlAssinado, Venda $venda, $chave)
    {
        Log::info('🌐 [SIMULAÇÃO] Simulando envio para SEFAZ');
        
        sleep(1); // Simula processamento
        
        return [
            'success' => true,
            'numero_nota' => $this->proximoNumeroNota(),
            'chave_acesso' => $chave,
            'numero_protocolo' => '123456789012345',
            'xml' => $xmlAssinado,
            'mensagem' => '✅ [SIMULAÇÃO] NF-e autorizada com sucesso - MODO TESTE'
        ];
    }

    private function gerarChaveAcessoSimulada()
    {
        $uf = '15';
        $ano = date('y');
        $mes = date('m');
        $cnpj = config('nfe.emitente_cnpj', '99999999000191');
        $modelo = '55';
        $serie = '001';
        $numero = str_pad($this->proximoNumeroNota(), 9, '0', STR_PAD_LEFT);
        $tpEmis = '1';
        $codigo = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        
        $chave = $uf . $ano . $mes . $cnpj . $modelo . $serie . $numero . $tpEmis . $codigo;
        $chave .= $this->calcularDigitoVerificador($chave);
        
        return $chave;
    }

    private function calcularDigitoVerificador($chave)
    {
        $pesos = [2, 3, 4, 5, 6, 7, 8, 9];
        $soma = 0;
        
        for ($i = 0; $i < strlen($chave); $i++) {
            $soma += intval($chave[$i]) * $pesos[$i % count($pesos)];
        }
        
        $resto = $soma % 11;
        return ($resto == 0 || $resto == 1) ? 0 : 11 - $resto;
    }
}