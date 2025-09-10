<?php

namespace App\Livewire\Components\App;

use App\Models\Product;
use App\Models\Nota;
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
    public $notaInfo = [
        'numero' => null,
        'valor' => null,
        'fornecedor' => null
    ];

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
                    return $item['prod'] ?? [];
                })->toArray();

            } catch (\Exception $e) {
                $this->addError('xmlFile', 'Erro ao processar o XML: ' . $e->getMessage());
                $this->reset(['notaInfo', 'produtos']);
            }
        }
    }

    public function salvar()
    {
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

        $nota = Nota::create([
            'numero_nota'    => $this->numero_nota,
            'valor_total'    => $this->valor_total,
            'fornecedor_uuid'=> $this->fornecedor_uuid,
        ]);

        foreach ($this->produtos as $produto) {
            if (
                !isset($produto['cProd'], $produto['xProd'], $produto['vUnCom'], $produto['qCom']) ||
                !is_numeric($produto['vUnCom']) || !is_numeric($produto['qCom'])
            ) {
                continue;
            }

            $registro = Product::where('codigo', $produto['cProd'])->first();

            if ($registro) {
                $registro->update([
                    'nome_titulo'     => $produto['xProd'],
                    'preco'           => $produto['vUnCom'],
                    'estoque'         => $registro->estoque + $produto['qCom'],
                    'fornecedor_uuid' => $this->fornecedor_uuid,
                ]);
            } else {
                Product::create([
                    'codigo'          => $produto['cProd'],
                    'nome_titulo'     => $produto['xProd'],
                    'preco'           => $produto['vUnCom'],
                    'estoque'         => $produto['qCom'],
                    'fornecedor_uuid' => $this->fornecedor_uuid,
                ]);
            }
        }

        session()->flash('success', 'Nota e produtos salvos com sucesso!');
        return redirect()->route('nota.index')->with('message', 'Entrada registrada com sucesso');
    }

    public function render()
    {
        return view('livewire.components.app.nota-produtos');
    }
}