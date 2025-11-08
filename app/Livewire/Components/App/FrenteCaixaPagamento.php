<?php

namespace App\Livewire\Components\App;

use App\Enums\FormaPagamentoEnum;
use App\Enums\BandeiraCartaoEnum;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Product;
use App\Services\Nota\NFeGenerateService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class FrenteCaixaPagamento extends Component
{
    // use NFeGenerateSerie, NFeGenerateNumber;

    public $carrinho = [];
    public $total = 0;
    public $descontoGeral = 0;
    public $tipoDescontoGeral = 'percentual';
    public $cliente = null; // Agora obrigatório
    public $usuario = null;
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

        if (!isset($vendaDados['usuario']) || !$vendaDados['usuario']) {
            session()->flash('error', 'Usuario não selecionado. Por favor, selecione um cliente antes de finalizar a venda.');
            return redirect()->route('frente-caixa');
        }

        $this->carrinho = $vendaDados['carrinho'];
        $this->total = $vendaDados['total'];
        $this->descontoGeral = $vendaDados['desconto_geral'];
        $this->tipoDescontoGeral = $vendaDados['tipo_desconto_geral'];
        $this->cliente = $vendaDados['cliente']; // Agora obrigatório
        $this->usuario = $vendaDados['usuario']; // Agora obrigatório
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
            // 1. Cria a venda
            $venda = Venda::create([
                'uuid' => Str::uuid(),
                'usuario_uuid' => $this->usuario['uuid'],
                'cliente_uuid' => $this->cliente['uuid'],
                'forma_pagamento' => $this->formaPagamento,
                'bandeira_cartao' => $this->ehCartao ? $this->bandeiraCartao : null,
                'quantidade_parcelas' => $this->ehCartao ? $this->parcelas : null,
                'valor_total' => $this->total,
                'valor_recebido' => $this->ehDinheiro ? $this->valorRecebido : $this->total,
                'troco' => $this->ehDinheiro ? $this->troco : 0,
                'numero_nota_fiscal' => $this->proximoNumeroNota(),
                'serie_nfe' => $this->seriePadrao(),
                'status' => 'finalizada', // valor inicial
                'observacoes' => $this->observacoes,
                'data_venda' => now(),
            ]);

            // 2. Cria os itens da venda
            foreach ($this->carrinho as $item) {
                $valor_total = $item['subtotal'] * $item['quantidade'];
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

            // 3. Processar NF-e
            $nfeService = new NFeGenerateService();
            $resultado = $nfeService->emitirNFe($venda);
            Log::info("📋 Resultado NF-e:", $resultado);

            DB::commit();

            // 4. Impressão e mensagens baseadas no tipo de retorno
            $dadosCupom = $this->getDadosImpressao($venda->id);
            // $tipo = $resultado['tipo'] ?? 'erro';
            if (($resultado['success'] ?? false) === true) {
                $tipo = 'autorizada';
            } elseif (($resultado['codigo_erro'] ?? '') === 'CONTINGENCIA') {
                $tipo = 'contingencia';
            } else {
                $tipo = 'rejeitada';
            }

            switch ($tipo) {
                case 'autorizada':
                    $this->imprimirVenda($venda->id, $dadosCupom['print_data'], false);

                    session()->forget('venda_dados');

                    $mensagem = "Venda finalizada e NF-e autorizada | Cliente: {$this->cliente['nome']} | " .
                                "Venda: {$venda->uuid} | Protocolo: {$resultado['numero_protocolo']}";

                    session()->flash('success', [
                        'title' => 'Venda finalizada com sucesso!',
                        'message' => $mensagem
                    ]);

                    Log::info("🎯 NF-e AUTORIZADA - Venda {$venda->uuid}");
                    break;

                case 'contingencia':
                    $this->imprimirVenda($venda->id, $dadosCupom['print_data'], true);

                    $mensagem = "Venda finalizada em contingência (offline) | Cliente: {$this->cliente['nome']} | " .
                                "Venda: {$venda->uuid} | Cupom emitido offline. Reenvio pendente.";

                    session()->flash('warning', [
                        'title' => 'Venda emitida em contingência',
                        'message' => $mensagem
                    ]);

                    Log::warning("⚠️ NF-e em contingência - Venda {$venda->uuid}");
                    break;

                case 'rejeitada':
                    $this->imprimirVenda($venda->id, $dadosCupom['print_data'], true);

                    $mensagem = "Venda registrada, mas NF-e rejeitada pela SEFAZ | Erro: {$resultado['erro']}";

                    session()->flash('error', [
                        'title' => 'NF-e rejeitada',
                        'message' => $mensagem
                    ]);

                    Log::warning("❌ NF-e REJEITADA - Venda {$venda->uuid}");
                    break;

                default:
                    throw new \Exception("Erro inesperado na emissão da NF-e");
            }

            return redirect()->route('dashboard.index');

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

    // No seu FrenteCaixaPagamento.php - mantenha apenas a rota API
    public function getDadosImpressao($vendaId)
    {
        $venda = Venda::findOrFail($vendaId);
        
        // Retorna os dados básicos da venda formatados
        return [
            'success' => true,
            'print_data' => [
                'empresa' => [
                    'nome' => config('nfe.razao_social', 'Casa Arari LTDA'),
                    'endereco' => config('nfe.logradouro', 'Rua Exemplo, nfe.numero'),
                    'cidade' => config('nfe.municipio', 'Belém/PA'),
                    'cnpj' => config('nfe.cnpj', '00.000.000/0001-00'),
                    'telefone' => config('nfe.telefone', '(91) 9999-9999')
                ],
                'venda' => [
                    'numero' => $venda->id,
                    'uuid' => $venda->uuid,
                    'data' => $venda->created_at->format('d/m/Y H:i:s'),
                    'cliente' => $venda->cliente ? $venda->cliente->nome : 'CONSUMIDOR FINAL',
                    'cpf_cnpj' => $venda->cliente ? $venda->cliente->cpf : '',
                ],
                'itens' => $venda->itens->map(function($item) {
                    return [
                        'descricao' => $item->produto->nome_titulo,
                        'quantidade' => $item->quantidade,
                        'valor_unitario' => $item->preco_unitario,
                        'valor_total' => $item->preco_total
                    ];
                }),
                'totais' => [
                    'subtotal' => $venda->valor_total, // Ou calcule o subtreal se tiver desconto
                    'desconto' => 0, // Ajuste conforme sua lógica
                    'total' => $venda->valor_total
                ],
                'pagamentos' => [[
                    'forma' => $venda->forma_pagamento,
                    'valor' => $venda->valor_total
                ]],
                'contingencia' => request('contingencia', false),
                'nfe' => [
                    'numero' => $venda->numero_nota_fiscal,
                    'serie' => $venda->serie_nfe,
                    'chave' => $venda->chave_acesso // Se tiver este campo
                ]
            ]
        ];
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
        
        return $ultimaNFe ? intval($ultimaNFe->numero_nota_fiscal) + 1 : 1350; // ← Começar de 1003
    }

    private function imprimirVenda(int $vendaId, array $dadosCupom, bool $contingencia = false, string $impressora = '71840'): bool
    {
        // Montar o texto do cupom
        $texto = $this->gerarTextoCupom($dadosCupom, $contingencia);

        try {
            $response = Http::timeout(5)->post('http://host.docker.internal:8050/api/imprimir-direto', [
                'texto' => $texto,
                'venda_id' => $vendaId,
                'impressora' => $impressora,
            ]);

            if ($response->successful() && $response->json('success')) {
                Log::info("✅ Cupom enviado para impressão venda #{$vendaId}" . ($contingencia ? " (Contingência)" : ""));
                return true;
            } else {
                Log::warning("❌ Falha na impressão venda #{$vendaId}", [
                    'response' => $response->body(),
                    'contingencia' => $contingencia
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("❌ Erro ao imprimir venda #{$vendaId}", [
                'error' => $e->getMessage(),
                'contingencia' => $contingencia
            ]);
            return false;
        }
    }

    /**
     * Gera o texto simples para o cupom (mesma lógica que você tinha no JS)
     */
    private function gerarTextoCupom(array $dadosCupom, bool $contingencia = false): string
    {
        $texto = "";

        // Carrega dados do emitente do config/nfe.php
        $nfeConfig = config('nfe');

        $razaoSocial = $nfeConfig['razao_social'] ?? 'EMPRESA';
        $nomeFantasia = $nfeConfig['nome_fantasia'] ?? '';
        $cnpj = $nfeConfig['cnpj'] ?? '';
        $ie = $nfeConfig['ie'] ?? '';
        $logradouro = $nfeConfig['logradouro'] ?? '';
        $numero = $nfeConfig['numero'] ?? '';
        $bairro = $nfeConfig['bairro'] ?? '';
        $municipio = $nfeConfig['municipio'] ?? '';
        $uf = $nfeConfig['uf'] ?? '';
        $cep = $nfeConfig['cep'] ?? '';
        $telefone = $nfeConfig['telefone'] ?? '';

        // Cabeçalho (empresa)
        $texto .= "      {$nomeFantasia}      \n";
        $texto .= "{$razaoSocial}\n";
        $texto .= "CNPJ: {$cnpj}\n";
        $texto .= "IE: {$ie}\n";
        $texto .= "{$logradouro}, {$numero} - {$bairro}\n";
        $texto .= "{$municipio} - {$uf}  CEP: {$cep}\n";
        if (!empty($telefone)) {
            $texto .= "TEL: {$telefone}\n";
        }
        $texto .= "--------------------------------\n";
        $texto .= "          CUPOM FISCAL          \n";
        $texto .= "================================\n";

        // Dados da venda
        $texto .= "Nº VENDA: {$dadosCupom['venda']['numero']}\n";
        $texto .= "DATA: {$dadosCupom['venda']['data']}\n";
        if (!empty($dadosCupom['venda']['cliente'])) {
            $texto .= "CLIENTE: {$dadosCupom['venda']['cliente']}\n";
        }
        if (!empty($dadosCupom['venda']['cpf_cnpj'])) {
            $texto .= "CPF/CNPJ: {$dadosCupom['venda']['cpf_cnpj']}\n";
        }
        $texto .= "--------------------------------\n";
        $texto .= "CÓD  DESCRIÇÃO           QTD  VL UN  VL TOT\n";
        $texto .= "--------------------------------\n";

        // Itens
        foreach ($dadosCupom['itens'] as $i => $item) {
            $codigo = str_pad($item['codigo'] ?? ($i + 1), 4, '0', STR_PAD_LEFT);
            $descricao = mb_strimwidth($item['descricao'], 0, 18, '');
            $qtd = number_format($item['quantidade'], 2, ',', '');
            $vlUnit = number_format($item['valor_unitario'], 2, ',', '');
            $vlTotal = number_format($item['valor_total'], 2, ',', '');

            $texto .= sprintf("%-4s %-18s %4s %6s %7s\n",
                $codigo, $descricao, $qtd, $vlUnit, $vlTotal
            );
        }

        $texto .= "--------------------------------\n";

        // Totais
        $subtotal = number_format($dadosCupom['totais']['subtotal'], 2, ',', '');
        $desconto = number_format($dadosCupom['totais']['desconto'], 2, ',', '');
        $total = number_format($dadosCupom['totais']['total'], 2, ',', '');

        $texto .= "SUBTOTAL.............: R$ {$subtotal}\n";
        if ($dadosCupom['totais']['desconto'] > 0) {
            $texto .= "DESCONTO.............: R$ {$desconto}\n";
        }
        $texto .= "================================\n";
        $texto .= "TOTAL A PAGAR........: R$ {$total}\n";
        $texto .= "================================\n";

        // Pagamentos
        $texto .= "FORMA(S) DE PAGAMENTO:\n";
        foreach ($dadosCupom['pagamentos'] as $pagamento) {
            $valor = number_format($pagamento['valor'], 2, ',', '');
            $texto .= "  {$pagamento['forma']}: R$ {$valor}\n";
        }

        $texto .= "--------------------------------\n";

        // NFC-e / Dados fiscais
        if (!empty($dadosCupom['nfe']['numero']) && $dadosCupom['nfe']['numero'] !== 'N/A') {
            $texto .= "NFC-e Nº: {$dadosCupom['nfe']['numero']}\n";
            $texto .= "SÉRIE: {$dadosCupom['nfe']['serie']}\n";
            if (!empty($dadosCupom['nfe']['protocolo']) && $dadosCupom['nfe']['protocolo'] !== 'N/A') {
                $texto .= "PROTOCOLO: {$dadosCupom['nfe']['protocolo']}\n";
            }
        }

        // Contingência / Homologação
        if ($contingencia) {
            $texto .= "\n*** EMITIDA EM CONTINGÊNCIA ***\n";
            $texto .= "SEM COMUNICAÇÃO COM A SEFAZ\n";
        }

        if (!empty($nfeConfig['ambiente']) && $nfeConfig['ambiente'] == 2) {
            $texto .= "\n*** AMBIENTE DE HOMOLOGAÇÃO ***\n";
        }

        $texto .= "\n--------------------------------\n";

        // QR Code (texto substituto)
        if (!empty($dadosCupom['nfe']['qrcode'])) {
            $texto .= "Consulta via QR Code:\n";
            $texto .= "{$dadosCupom['nfe']['qrcode']}\n";
        }

        $texto .= "\n--------------------------------\n";
        $texto .= "OBRIGADO PELA PREFERÊNCIA!\n";
        $texto .= "Volte sempre :)\n";
        $texto .= "================================\n\n\n";

        return $texto;
    }

    public function render()
    {
        return view('livewire.components.app.frente-caixa-pagamento');
    }
}