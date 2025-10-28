<?php

namespace App\Livewire\Components\App;

use App\Enums\FormaPagamentoEnum;
use App\Enums\BandeiraCartaoEnum;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Product;
use App\Services\Nota\NFeGenerateService;
use App\Services\Nota\NFeIntegrationService;
use App\Services\Nota\NFeService;
use App\Traits\Nota\NFeGenerateNumber;
use App\Traits\Nota\NFeGenerateSerie;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class FrenteCaixaPagamento extends Component
{
    // use NFeGenerateSerie, NFeGenerateNumber;

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
                'numero_nota_fiscal' => $this->proximoNumeroNota(),
                'serie_nfe' => $this->seriePadrao(),
                'status' => 'finalizada',
                'observacoes' => $this->observacoes,
                'data_venda' => now(),
            ]);

            // Cria os itens da venda
            foreach ($this->carrinho as $item) {
                $valor_total = $item['preco'] * $item['quantidade'];
                $valor_total_formatado = number_format($valor_total, 2, '.');

                VendaItem::create([
                    'uuid' => Str::uuid(),
                    'venda_uuid' => $venda->uuid,
                    'produto_uuid' => $item['uuid'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'preco_total' => $valor_total_formatado,
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

            Log::info("✅ Venda criada: {$venda->uuid}");

            // 2. Processar NF-e com NFeGenerateService
            $nfeService = new NFeGenerateService(); // ← Seu service atual
            $resultado = $nfeService->emitirNFe($venda);
            Log::info("📋 Resultado NF-e:", $resultado);
        
            if ($resultado['success']) {
                // ✅ A venda JÁ foi atualizada pelo emitirNFe() - apenas commit
                DB::commit();
                Log::info("🎯 NF-e AUTORIZADA - Commit realizado");
        
                session()->forget('venda_dados');
                
                $mensagemSucesso = 'Venda finalizada com sucesso! | Cliente: ' . $this->cliente['nome'] . 
                                  ' | Nº da Venda: ' . $venda->uuid . 
                                  ' | NFE: ' . $venda->numero_nota_fiscal .
                                  ' | Protocolo: ' . ($resultado['numero_protocolo'] ?? 'N/A');
                
                session()->flash('success', [
                    'title' => 'Venda finalizada com sucesso!',
                    'message' => $mensagemSucesso
                ]);
                
                Log::info("🔀 Redirecionando para dashboard");
                return redirect()->route('dashboard.index');
        
            } else {
                // ✅ A venda JÁ foi atualizada pelo emitirNFe() - apenas commit  
                DB::commit();
                Log::warning("⚠️ Venda finalizada mas NF-e rejeitada");
        
                $mensagemWarning = 'Venda finalizada, mas NF-e pendente | Cliente: ' . $this->cliente['nome'] . 
                                  ' | Venda: ' . $venda->uuid . 
                                  ' | Erro NF-e: ' . ($resultado['erro'] ?? $resultado['mensagem']) .
                                  ' | Contate o suporte.';
                
                session()->flash('warning', [
                    'title' => 'Venda finalizada, mas NF-e pendente',
                    'message' => $mensagemWarning
                ]);
                
                return redirect()->route('dashboard.index');
            }
        
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Exception no processamento: " . $e->getMessage());
            
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

    public function seriePadrao(): string
    {
        return '1'; // Série principal
    }

    public function proximoNumeroNota(): int
    {
        $ultimaNFe = Venda::whereNotNull('numero_nota_fiscal')
                        ->orderBy('numero_nota_fiscal', 'desc') // ← CORREÇÃO: order by numero, não created_at
                        ->first();
        
        return $ultimaNFe ? intval($ultimaNFe->numero_nota_fiscal) + 1 : 1003; // ← Começar de 1003
    }

    public function render()
    {
        return view('livewire.components.app.frente-caixa-pagamento');
    }
}