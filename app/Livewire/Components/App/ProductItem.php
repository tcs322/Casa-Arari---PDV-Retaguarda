<?php

namespace App\Livewire\Components\App;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductItem extends Component
{
    use WithFileUploads;

    public $xmlFile;
    public $produtos = [];
    public $emitenteCnpj;
    public $fornecedor_uuid;

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

                // Captura CNPJ do emitente
                $cnpj = $nfeArray['infNFe']['emit']['CNPJ'] ?? null;
                $this->emitenteCnpj = $cnpj;

                // Captura os produtos
                $detList = $nfeArray['infNFe']['det'] ?? [];

                if (isset($detList['prod'])) {
                    $detList = [$detList]; // garante que seja array
                }

                $this->produtos = collect($detList)->map(function ($item) {
                    return $item['prod'] ?? [];
                })->toArray();

            } catch (\Exception $e) {
                $this->addError('xmlFile', 'Erro ao processar o XML: ' . $e->getMessage());
            }
        }
    }


    public function salvar()
    {
        // Captura o CNPJ do emitente do primeiro produto
        $cnpj = $this->emitenteCnpj ?? null;

        if (!$cnpj) {
            $this->addError('xmlFile', 'CNPJ do fornecedor não encontrado no XML.');
            return;
        }

        // Consulta o fornecedor pela base
        $fornecedor = \App\Models\Fornecedor::where('documento', $cnpj)->first();

        // Se não encontrou, bloqueia
        if (!$fornecedor) {
            $this->addError('xmlFile', 'Fornecedor com CNPJ ' . $cnpj . ' não foi encontrado na base.');
            return;
        }

        // Define o UUID para uso posterior
        $this->fornecedor_uuid = $fornecedor->uuid;

        foreach ($this->produtos as $produto) {
            if (
                !isset($produto['cProd'], $produto['xProd'], $produto['vUnCom'], $produto['qCom']) ||
                !is_numeric($produto['vUnCom']) || !is_numeric($produto['qCom'])
            ) {
                continue;
            }

            $registro = \App\Models\Product::where('codigo', $produto['cProd'])->first();

            if ($registro) {
                $registro->update([
                    'nome_titulo'     => $produto['xProd'],
                    'preco'           => $produto['vUnCom'],
                    'estoque'         => $registro->estoque + $produto['qCom'],
                    'fornecedor_uuid' => $this->fornecedor_uuid,
                    'autor' => 'autor 1',
                    'edicao' => 1
                ]);
            } else {
                \App\Models\Product::create([
                    'codigo'          => $produto['cProd'],
                    'nome_titulo'     => $produto['xProd'],
                    'preco'           => $produto['vUnCom'],
                    'estoque'         => $produto['qCom'],
                    'fornecedor_uuid' => $this->fornecedor_uuid,
                    'autor' => 'autor 1',
                    'edicao' => 1
                ]);
            }
        }

        session()->flash('success', 'Produtos salvos com sucesso!');
        return redirect()->route('nota.index')->with('message', 'Entrada registrada');;
    }



    public function render()
    {
        return view('livewire.components.app.product-item');
    }
}
