<?php

namespace App\Livewire\Components\App;

use App\Models\Product;
use App\Models\Nota;
use App\Enums\TipoProdutoEnum;
use App\Services\Product\ProductTributacaoService;
use Livewire\Component;
use Livewire\WithFileUploads;

class NotaProdutos extends Component
{
    use WithFileUploads;

    public $xmlFile;
    public $produtos = [];
    public $emitenteCnpj;
    public $emitenteNome;
    public $fornecedor_uuid;
    public $numero_nota;
    public $valor_total;
    public $tipo_nota; // ✅ NOVO CAMPO
    public $notaInfo = [
        'numero' => null,
        'valor' => null,
        'fornecedor' => null
    ];

    protected $productTributacaoService;

    public function boot()
    {
        $this->productTributacaoService = new ProductTributacaoService();
    }

    public function updated($property)
    {
        if ($property === 'xmlFile') {
            if (!$this->xmlFile?->getRealPath()) {
                return;
            }

            try {
                $xmlContent = file_get_contents($this->xmlFile->getRealPath());
                $xmlObject = simplexml_load_string($xmlContent, "SimpleXMLElement", LIBXML_NOCDATA);
                $array = json_decode(json_encode($xmlObject), true);

                $nfeArray = $array['NFe'] ?? $array;

                $this->emitenteCnpj = $nfeArray['infNFe']['emit']['CNPJ'] ?? null;
                $this->emitenteNome = $nfeArray['infNFe']['emit']['xNome'] ?? null;

                $this->numero_nota = $nfeArray['infNFe']['ide']['nNF'] ?? null;
                $this->valor_total = $nfeArray['infNFe']['total']['ICMSTot']['vNF'] ?? null;

                $this->notaInfo = [
                    'numero' => $this->numero_nota,
                    'valor' => $this->valor_total ? number_format($this->valor_total, 2, ',', '.') : null,
                    'fornecedor' => $this->emitenteNome
                ];

                $detList = $nfeArray['infNFe']['det'] ?? [];

                if (isset($detList['prod']) && !isset($detList[0])) {
                    $detList = [$detList];
                }

                $this->produtos = collect($detList)->map(function ($item) {
                    $produto = $item['prod'] ?? [];
                    
                    // ✅ Extrai dados fiscais do XML
                    if (isset($item['imposto'])) {
                        $produto['imposto'] = $item['imposto'];
                    }
                    
                    return $produto;
                })->toArray();

                // ✅ Tenta detectar automaticamente o tipo da nota
                $this->detectarTipoNota();

            } catch (\Exception $e) {
                $this->addError('xmlFile', 'Erro ao processar o XML: ' . $e->getMessage());
                $this->reset(['notaInfo', 'produtos', 'tipo_nota']);
            }
        }
    }

    /**
     * Tenta detectar automaticamente o tipo da nota baseado nos produtos
     */
    private function detectarTipoNota()
{
    if (empty($this->produtos)) {
        return;
    }

    // Analisa todos os produtos para detectar o tipo
    $tiposDetectados = [];
    
    foreach ($this->produtos as $produto) {
        $ncm = $produto['NCM'] ?? '';
        $descricao = strtolower($produto['xProd'] ?? '');
        
        $tipoDetectado = $this->analisarProduto($ncm, $descricao);
        if ($tipoDetectado) {
            $tiposDetectados[] = $tipoDetectado;
        }
    }

    // Conta as ocorrências e decide pelo tipo mais frequente
    if (!empty($tiposDetectados)) {
        $contagem = array_count_values($tiposDetectados);
        arsort($contagem);
        $this->tipo_nota = array_key_first($contagem);
    } else {
        $this->tipo_nota = TipoProdutoEnum::LIVRARIA; // Default
    }
}

    /**
     * Analisa um produto individualmente para detectar o tipo
     */
    private function analisarProduto(string $ncm, string $descricao): ?string
    {
        // ✅ CAFETERIA - Bebidas e alimentos
        $cafeteriaNcms = ['2202', '2201', '0901', '1905', '2009', '2106'];
        $cafeteriaKeywords = [
            'refrigerante', 'bebida', 'água', 'agua', 'suco', 'cerveja', 
            'café', 'cafe', 'sanduíche', 'sanduiche', 'lanche', 'bolo',
            'salgado', 'salgados', 'pão', 'pao', 'torta', 'sorvete',
            'coca', 'guaraná', 'guarana', 'pepsi', 'fanta', 'sprite'
        ];
        
        // ✅ LIVRARIA - Livros e material de leitura
        $livrariaNcms = ['4901'];
        $livrariaKeywords = [
            'livro', 'revista', 'jornal', 'enciclopédia', 'dicionário',
            'romance', 'conto', 'poesia', 'biografia', 'didático'
        ];
        
        // ✅ PAPELARIA - Material escolar/escritório
        $papelariaNcms = ['4820', '9608', '9609'];
        $papelariaKeywords = [
            'caderno', 'caneta', 'lápis', 'lapis', 'borracha', 'régua',
            'mochila', 'estojo', 'papel', 'cola', 'tesoura', 'grafite'
        ];

        // Verifica por NCM primeiro (mais confiável)
        foreach ($cafeteriaNcms as $ncmCafe) {
            if (str_starts_with($ncm, $ncmCafe)) {
                return TipoProdutoEnum::CAFETERIA;
            }
        }
        
        foreach ($livrariaNcms as $ncmLivro) {
            if (str_starts_with($ncm, $ncmLivro)) {
                return TipoProdutoEnum::LIVRARIA;
            }
        }
        
        foreach ($papelariaNcms as $ncmPapel) {
            if (str_starts_with($ncm, $ncmPapel)) {
                return TipoProdutoEnum::PAPELARIA;
            }
        }

        // Se NCM não detectou, verifica por palavras-chave na descrição
        foreach ($cafeteriaKeywords as $keyword) {
            if (str_contains($descricao, $keyword)) {
                return TipoProdutoEnum::CAFETERIA;
            }
        }
        
        foreach ($livrariaKeywords as $keyword) {
            if (str_contains($descricao, $keyword)) {
                return TipoProdutoEnum::LIVRARIA;
            }
        }
        
        foreach ($papelariaKeywords as $keyword) {
            if (str_contains($descricao, $keyword)) {
                return TipoProdutoEnum::PAPELARIA;
            }
        }

        return null;
    }

    public function salvar()
    {
        $this->validate([
            'tipo_nota' => ['required', 'in:' . implode(',', TipoProdutoEnum::getValues())],
        ]);

        $cnpj = $this->emitenteCnpj ?? null;

        if (!$cnpj) {
            $this->addError('xmlFile', 'CNPJ do fornecedor não encontrado no XML.');
            return;
        }

        $fornecedor = \App\Models\Fornecedor::where('documento', $cnpj)->first();

        if (!$fornecedor) {
            $this->addError('xmlFile', 'Fornecedor com CNPJ ' . $cnpj . ' não foi encontrado na base.');
            return;
        }

        $this->fornecedor_uuid = $fornecedor->uuid;

        if (Nota::where('numero_nota', $this->numero_nota)->exists()) {
            $this->addError('numero_nota', 'Esta nota fiscal já foi cadastrada anteriormente.');
            return;
        }

        // Cria a nota
        $nota = Nota::create([
            'numero_nota'    => $this->numero_nota,
            'valor_total'    => $this->valor_total,
            'fornecedor_uuid'=> $this->fornecedor_uuid,
            'tipo_nota'      => $this->tipo_nota, // ✅ Salva o tipo da nota
        ]);

        // Processa cada produto
        foreach ($this->produtos as $produto) {
            if (
                !isset($produto['cProd'], $produto['xProd'], $produto['vUnCom'], $produto['qCom']) ||
                !is_numeric($produto['vUnCom']) || !is_numeric($produto['qCom'])
            ) {
                continue;
            }

            $this->processarProduto($produto, $nota);
        }

        return redirect()->route('nota.index')->with('message', 'Entrada registrada com sucesso');
    }

    /**
     * Processa individualmente cada produto aplicando tributação automática
     */
    private function processarProduto(array $produto, Nota $nota)
    {
        $registro = Product::where('codigo', $produto['cProd'])->first();

        // Dados base do produto
        $dadosProduto = [
            'codigo'          => $produto['cProd'],
            'nome_titulo'     => $produto['xProd'],
            'preco_compra'    => $produto['vUnCom'],
            'preco_venda'    => $produto['vUnCom'],
            'estoque'         => $produto['qCom'],
            'fornecedor_uuid' => $this->fornecedor_uuid,
            'nota_uuid'       => $nota->uuid,
            'tipo'            => $this->tipo_nota,
            'tipo_producao'   => $this->definirTipoProducao($produto),
        ];

        // ✅ Aplica tributação automática baseada no tipo da nota
        $dadosProduto = $this->aplicarTributacaoProduto($dadosProduto);

        if ($registro) {
            // Atualiza estoque e mantém tributação existente
            $registro->update([
                'nome_titulo'     => $dadosProduto['nome_titulo'],
                'preco_compra'    => $dadosProduto['preco_compra'],
                'preco_venda'    => $produto['vUnCom'],
                'estoque'         => $registro->estoque + $dadosProduto['estoque'],
                'fornecedor_uuid' => $dadosProduto['fornecedor_uuid'],
                // Não atualiza campos fiscais para produtos existentes
            ]);
        } else {
            // Cria novo produto com tributação automática
            Product::create($dadosProduto);
        }
    }

    /**
     * Define o tipo de produção baseado no tipo da nota e dados do produto
     */
    private function definirTipoProducao(array $produto): ?string
    {
        // Para cafeteria, tenta detectar se é artesanal ou industrial
        if ($this->tipo_nota === TipoProdutoEnum::CAFETERIA) {
            $descricao = strtolower($produto['xProd'] ?? '');
            $ncm = $produto['NCM'] ?? '';
            
            // Produtos artesanais geralmente têm descrições específicas
            $artesanalKeywords = ['artesanal', 'caseiro', 'natural', 'tradicional'];
            
            foreach ($artesanalKeywords as $keyword) {
                if (str_contains($descricao, $keyword)) {
                    return \App\Enums\TipoProducaoProdutoEnum::ARTESANAL;
                }
            }
            
            return \App\Enums\TipoProducaoProdutoEnum::INDUSTRIAL;
        }

        // Para outros tipos, retorna null
        return null;
    }

    /**
     * Aplica tributação automática ao produto
     */
    private function aplicarTributacaoProduto(array $dadosProduto): array
    {
        try {
            // ✅ CORREÇÃO: Usa o método público aplicarTributacaoArray
            return $this->productTributacaoService->aplicarTributacaoArray($dadosProduto);

        } catch (\Exception $e) {
            // Em caso de erro, usa tributação padrão
            return array_merge($dadosProduto, $this->getTributacaoPadrao());
        }
    }

    /**
     * Tributação padrão (fallback)
     */
    private function getTributacaoPadrao(): array
    {
        return [
            'ncm' => '49019900',
            'cest' => '2800300',
            'codigo_barras' => null,
            'unidade_medida' => 'UN',
            'aliquota_icms' => 17.0,
            'cst_icms' => '00',
            'cst_pis' => '01',
            'cst_cofins' => '01',
            'cfop' => '5102',
            'origem' => '0'
        ];
    }

    public function render()
    {
        return view('livewire.components.app.nota-produtos', [
            'tiposNota' => TipoProdutoEnum::getInstances()
        ]);
    }
}