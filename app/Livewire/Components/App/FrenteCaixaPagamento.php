<?php

namespace App\Livewire\Components\App;

use App\Enums\FormaPagamentoEnum;
use App\Enums\BandeiraCartaoEnum;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Product;
use App\Services\Nota\NFeIntegrationService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FrenteCaixaPagamento extends Component
{
    public $carrinho = [];
    public $total = 0;
    public $descontoGeral = 0;
    public $tipoDescontoGeral = 'percentual';
    public $cliente = null; // Agora obrigatório
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

        // Valida se há cliente selecionado
        if (!isset($vendaDados['cliente']) || !$vendaDados['cliente']) {
            session()->flash('error', 'Cliente não selecionado. Por favor, selecione um cliente antes de finalizar a venda.');
            return redirect()->route('frente-caixa');
        }

        $this->carrinho = $vendaDados['carrinho'];
        $this->total = $vendaDados['total'];
        $this->descontoGeral = $vendaDados['desconto_geral'];
        $this->tipoDescontoGeral = $vendaDados['tipo_desconto_geral'];
        $this->cliente = $vendaDados['cliente']; // Agora obrigatório
    }

    public function updatedFormaPagamento($value)
    {
        $this->ehCartao = $value && in_array($value, [
            FormaPagamentoEnum::CARTAO_CREDITO,
            FormaPagamentoEnum::CARTAO_DEBITO
        ]);

        $this->ehDinheiro = $value === FormaPagamentoEnum::DINHEIRO;

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
        // Validação reforçada para garantir que o cliente está presente
        if (!$this->cliente || !isset($this->cliente['uuid'])) {
            session()->flash('error', [
                'title' => 'Erro de validação',
                'message' => 'Cliente não selecionado. Por favor, volte e selecione um cliente.'
            ]);
            return;
        }

        $this->validate([
            'formaPagamento' => 'required|in:' . implode(',', FormaPagamentoEnum::getValues()),
        ]);

        if ($this->ehCartao) {
            $this->validate([
                'bandeiraCartao' => 'required|in:' . implode(',', BandeiraCartaoEnum::getValues()),
                'parcelas' => 'required|integer|min:1|max:12',
            ]);
        }

        if ($this->ehDinheiro) {
            $this->validate([
                'valorRecebido' => 'required|numeric|min:' . $this->total,
            ]);
        }

        DB::beginTransaction();

        try {
            // Cria a venda com o cliente_uuid (agora obrigatório)
            $venda = Venda::create([
                'uuid' => Str::uuid(),
                'usuario_uuid' => Auth::user()->uuid,
                'cliente_uuid' => $this->cliente['uuid'], // Agora obrigatório
                'forma_pagamento' => $this->formaPagamento,
                'bandeira_cartao' => $this->ehCartao ? $this->bandeiraCartao : null,
                'quantidade_parcelas' => $this->ehCartao ? $this->parcelas : null,
                'valor_total' => $this->total,
                'valor_recebido' => $this->ehDinheiro ? $this->valorRecebido : $this->total,
                'troco' => $this->ehDinheiro ? $this->troco : 0,
                'numero_nota_fiscal' => null,
                'status' => 'finalizada',
                'observacoes' => $this->observacoes,
                'data_venda' => now(),
            ]);

            // Cria os itens da venda
            foreach ($this->carrinho as $item) {
                VendaItem::create([
                    'uuid' => Str::uuid(),
                    'venda_uuid' => $venda->uuid,
                    'produto_uuid' => $item['uuid'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'subtotal' => $item['subtotal'],
                    'desconto' => $item['desconto'] ?? 0,
                    'tipo_desconto' => $item['tipo_desconto'] ?? 'percentual'
                ]);

                // Atualiza estoque
                $produto = Product::where('uuid', $item['uuid'])->first();
                if ($produto) {
                    $produto->decrement('estoque', $item['quantidade']);
                }
            }

            // ✅ AGORA EMITE A NF-e APÓS CRIAR A VENDA
            $nfeService = new NFeIntegrationService();
            $resultadoNFe = $nfeService->emitirNFe($venda);

            if ($resultadoNFe['success']) {
                // Atualiza a venda com os dados da NF-e
                $venda->update([
                    'numero_nota_fiscal' => $resultadoNFe['numero_nota'],
                    'chave_acesso_nfe' => $resultadoNFe['chave_acesso'],
                    'xml_nfe' => $resultadoNFe['xml'],
                    'status_nfe' => 'autorizada'
                ]);

                DB::commit();

                session()->forget('venda_dados');
                
                $mensagemSucesso = 'Venda finalizada com sucesso! | Cliente: ' . $this->cliente['nome'] . 
                                  ' | Nº da Venda: ' . $venda->uuid . 
                                  ' | NFE: ' . $resultadoNFe['numero_nota'];
                
                session()->flash('success', [
                    'title' => 'Venda finalizada com sucesso!',
                    'message' => $mensagemSucesso
                ]);
                
                return redirect()->route('dashboard.index');

            } else {
                // Se a NF-e falhar, mantém a venda mas marca como sem NF-e
                $venda->update([
                    'status_nfe' => 'erro',
                    'erro_nfe' => $resultadoNFe['erro']
                ]);

                DB::commit();

                $mensagemWarning = 'Venda finalizada, mas NF-e pendente | Cliente: ' . $this->cliente['nome'] . 
                                  ' | Venda: ' . $venda->uuid . 
                                  ' | Erro NF-e: ' . $resultadoNFe['mensagem'] .
                                  ' | Contate o suporte.';
                
                session()->flash('warning', [
                    'title' => 'Venda finalizada, mas NF-e pendente',
                    'message' => $mensagemWarning
                ]);
                
                return redirect()->route('dashboard.index');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            session()->flash('error', [
                'title' => 'Erro ao processar venda',
                'message' => 'Erro: ' . $e->getMessage()
            ]);
            
            return back();
        }
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