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

        // Verifica se o produto já está no carrinho
        $index = array_search($produtoId, array_column($this->carrinho, 'id'));
        
        if ($index !== false) {
            // Incrementa quantidade se já existir
            $this->carrinho[$index]['quantidade']++;
        } else {
            // Adiciona novo produto ao carrinho
            $this->carrinho[] = [
                'id' => $produto->id,
                'uuid' => $produto->uuid,
                'codigo' => $produto->codigo,
                'nome' => $produto->nome_titulo,
                'preco' => $produto->preco,
                'quantidade' => 1,
                'subtotal' => $produto->preco
            ];
        }

        $this->calcularTotal();
        $this->search = ''; // Limpa a busca
        $this->produtosEncontrados = []; // Limpa resultados
    }

    public function atualizarQuantidade($index, $quantidade)
    {
        if ($quantidade < 1) {
            unset($this->carrinho[$index]);
            $this->carrinho = array_values($this->carrinho); // Reindexa array
        } else {
            $this->carrinho[$index]['quantidade'] = $quantidade;
            $this->carrinho[$index]['subtotal'] = $this->carrinho[$index]['preco'] * $quantidade;
        }

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
        $this->totalCarrinho = array_sum(array_column($this->carrinho, 'subtotal'));
    }

    public function finalizarVenda()
    {
        // TODO: Implementar lógica de finalização de venda
        // Criar registro de venda, baixar estoque, etc.
        
        session()->flash('success', 'Venda finalizada com sucesso!');
        $this->reset(['carrinho', 'totalCarrinho', 'search', 'produtosEncontrados']);
    }

    public function render()
    {
        return view('livewire.components.app.frente-caixa');
    }
}