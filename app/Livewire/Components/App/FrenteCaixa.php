<?php

namespace App\Livewire\Components\App;

use App\Models\Product;
use Livewire\Component;

class FrenteCaixa extends Component
{
    public $search = '';
    public $produtosEncontrados = [];
    public $carrinho = [];
    public $totalCarrinho = 0;
    public $descontoGeral = 0;
    public $tipoDescontoGeral = 'percentual'; // 'percentual' ou 'valor'
    public $descontosIndividuais = [];

    public function buscarProdutos()
    {
        if (strlen($this->search) < 2) {
            $this->produtosEncontrados = [];
            return;
        }

        $this->produtosEncontrados = Product::where('nome_titulo', 'like', '%' . $this->search . '%')
            ->orWhere('codigo', 'like', '%' . $this->search . '%')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function adicionarAoCarrinho($produtoId)
    {
        $produto = Product::find($produtoId);
        
        if (!$produto) {
            return;
        }

        $index = array_search($produtoId, array_column($this->carrinho, 'id'));
        
        if ($index !== false) {
            $this->carrinho[$index]['quantidade']++;
        } else {
            $this->carrinho[] = [
                'id' => $produto->id,
                'uuid' => $produto->uuid,
                'codigo' => $produto->codigo,
                'nome' => $produto->nome_titulo,
                'preco' => $produto->preco,
                'quantidade' => 1,
                'subtotal' => $produto->preco,
                'desconto' => 0,
                'tipo_desconto' => 'percentual'
            ];
        }

        $this->calcularTotal();
        $this->search = '';
        $this->produtosEncontrados = [];
    }

    public function atualizarQuantidade($index, $quantidade)
    {
        if ($quantidade < 1) {
            unset($this->carrinho[$index]);
            $this->carrinho = array_values($this->carrinho);
        } else {
            $this->carrinho[$index]['quantidade'] = $quantidade;
            $this->carrinho[$index]['subtotal'] = $this->carrinho[$index]['preco'] * $quantidade;
        }

        $this->calcularTotal();
    }

    public function aplicarDescontoIndividual($index, $desconto, $tipo = 'percentual')
    {
        if (!isset($this->carrinho[$index])) {
            return;
        }

        $this->carrinho[$index]['desconto'] = floatval($desconto);
        $this->carrinho[$index]['tipo_desconto'] = $tipo;
        $this->calcularTotal();
    }

    public function aplicarDescontoGeral()
    {
        $this->calcularTotal();
    }

    public function removerDoCarrinho($index)
    {
        unset($this->carrinho[$index]);
        $this->carrinho = array_values($this->carrinho);
        $this->calcularTotal();
    }

    protected function calcularTotal()
    {
        $total = 0;

        foreach ($this->carrinho as $index => $item) {
            $subtotal = $item['preco'] * $item['quantidade'];
            
            // Aplica desconto individual
            if ($item['desconto'] > 0) {
                if ($item['tipo_desconto'] === 'percentual') {
                    $subtotal -= $subtotal * ($item['desconto'] / 100);
                } else {
                    $subtotal -= $item['desconto'];
                }
            }

            $this->carrinho[$index]['subtotal'] = max($subtotal, 0);
            $total += $this->carrinho[$index]['subtotal'];
        }

        // Aplica desconto geral
        if ($this->descontoGeral > 0) {
            if ($this->tipoDescontoGeral === 'percentual') {
                $total -= $total * ($this->descontoGeral / 100);
            } else {
                $total -= $this->descontoGeral;
            }
        }

        $this->totalCarrinho = max($total, 0);
    }

    public function finalizarVenda()
    {
        // TODO: Implementar lógica de finalização de venda
        session()->flash('success', 'Venda finalizada com sucesso!');
        $this->reset(['carrinho', 'totalCarrinho', 'search', 'produtosEncontrados', 'descontoGeral']);
    }

    public function render()
    {
        return view('livewire.components.app.frente-caixa');
    }
}