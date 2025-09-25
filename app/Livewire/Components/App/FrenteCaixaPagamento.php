<?php

namespace App\Livewire\Components\App;

use App\Enums\FormaPagamentoEnum;
use App\Enums\BandeiraCartaoEnum;
use Livewire\Component;

class FrenteCaixaPagamento extends Component
{
    public $carrinho = [];
    public $total = 0;
    public $descontoGeral = 0;
    public $tipoDescontoGeral = 'percentual';
    public $ehCartao = false;
    public $ehDinheiro = false;
    
    public $formaPagamento;
    public $bandeiraCartao;
    public $observacoes;
    public $valorRecebido = 0;
    public $troco = 0;
    public $parcelas = 1;
    public $valorParcela = 0;

    public function mount()
    {
        $vendaDados = session()->get('venda_dados');
        
        if (!$vendaDados) {
            return redirect()->route('frente-caixa');
        }

        $this->carrinho = $vendaDados['carrinho'];
        $this->total = $vendaDados['total'];
        $this->descontoGeral = $vendaDados['desconto_geral'];
        $this->tipoDescontoGeral = $vendaDados['tipo_desconto_geral'];
    }

    public function updatedFormaPagamento($value)
    {
        $this->ehCartao = $value && in_array($value, [
            FormaPagamentoEnum::CARTAO_CREDITO,
            FormaPagamentoEnum::CARTAO_DEBITO
        ]);

        $this->ehDinheiro = $value === FormaPagamentoEnum::DINHEIRO;

        // Resetar valores quando mudar a forma de pagamento
        $this->valorRecebido = 0;
        $this->troco = 0;
        $this->parcelas = 1;
        $this->calcularParcela();
    }

    public function updatedValorRecebido($value)
    {
        $valor = floatval(str_replace(['R$', '.', ','], ['', '', '.'], $value));
        $this->troco = max($valor - $this->total, 0);
    }

    public function updatedParcelas($value)
    {
        $this->parcelas = max(1, intval($value));
        $this->calcularParcela();
    }

    protected function calcularParcela()
    {
        if ($this->total > 0 && $this->parcelas > 0) {
            $this->valorParcela = $this->total / $this->parcelas;
        } else {
            $this->valorParcela = 0;
        }
    }

    public function processarPagamento()
    {
        $this->validate([
            'formaPagamento' => 'required|in:' . implode(',', FormaPagamentoEnum::getValues()),
        ]);

        // Validações específicas para cartão
        if ($this->ehCartao) {
            $this->validate([
                'bandeiraCartao' => 'required|in:' . implode(',', BandeiraCartaoEnum::getValues()),
                'parcelas' => 'required|integer|min:1|max:12',
            ]);
        }

        // Validações específicas para dinheiro
        if ($this->ehDinheiro) {
            $this->validate([
                'valorRecebido' => 'required|numeric|min:' . $this->total,
            ]);
        }

        // TODO: Processar venda no banco de dados
        $vendaData = [
            'carrinho' => $this->carrinho,
            'total' => $this->total,
            'forma_pagamento' => $this->formaPagamento,
            'bandeira_cartao' => $this->bandeiraCartao,
            'parcelas' => $this->parcelas,
            'valor_recebido' => $this->valorRecebido,
            'troco' => $this->troco,
            'observacoes' => $this->observacoes
        ];

        session()->forget('venda_dados');
        session()->flash('success', 'Venda processada com sucesso!');
        return redirect()->route('vendas.index');
    }

    public function voltarParaCarrinho()
    {
        return redirect()->route('frente-caixa');
    }

    public function render()
    {
        return view('livewire.components.app.frente-caixa-pagamento');
    }
}