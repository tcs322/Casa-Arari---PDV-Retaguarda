<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class PedidoController extends Controller
{
    /**
     * Retorna todos os pedidos pendentes (para a cozinha)
     */
    public function index()
    {
        return Pedido::where('status', 'pendente')
            ->latest()
            ->get();
    }

    /**
     * Cria um novo pedido (enviado pelo atendente)
     */
    public function store(Request $request)
    {
        // Validação dos campos
        $validated = $request->validate([
            'cliente_nome' => 'required|string|max:255',
            'itens' => 'required|array|min:1',
            'valor_total' => 'nullable|numeric|min:0', // 🆕 adiciona validação do total
        ]);

        // 🧮 Garante que o valor_total é calculado corretamente (mesmo que o front envie)
        $valorTotalCalculado = collect($validated['itens'])->reduce(function ($carry, $item) {
            $preco = $item['preco'] ?? 0;
            $quantidade = $item['quantidade'] ?? 1;
            return $carry + ($preco * $quantidade);
        }, 0);

        // 🧠 Usa o valor enviado, mas se for diferente, mantém o calculado
        $valorTotal = $validated['valor_total'] ?? $valorTotalCalculado;

        // Criação do pedido
        $pedido = Pedido::create([
            'cliente_nome' => $validated['cliente_nome'],
            'itens' => $validated['itens'],
            'valor_total' => $valorTotal,
            'status' => 'pendente',
        ]);

        return response()->json([
            'message' => '✅ Pedido criado com sucesso!',
            'pedido' => $pedido,
        ]);
    }

    /**
     * Marca um pedido como preparado
     */
    public function marcarComoPreparado(Pedido $pedido)
    {
        $pedido->status = 'preparado';

        $itens = $pedido->itens;

        foreach ($itens as &$item) {
            $item['quantidade_pendente'] = 0;
        }

        $pedido->itens = $itens;
        $pedido->save();

        return response()->json([
            'message' => 'Pedido preparado e pendências zeradas!',
        ]);
    }

    public function marcarComoPago(Pedido $pedido)
    {
        $pedido->status_pagamento = 'Pago';
        $pedido->save();

        return response()->json([
            'message' => 'Pedido marcado como pago',
        ]);
    }

    public function allPedidos()
    {
        return Pedido::whereDate('created_at', Carbon::today())->get();
    }

    private function mergeItens($itensExistentes, $itensNovos)
    {
        $resultado = [];

        // Indexa itens existentes por id
        foreach ($itensExistentes as $item) {
            $resultado[$item['id']] = $item;
        }

        // Mescla com itens novos
        foreach ($itensNovos as $novo) {

            if (isset($resultado[$novo['id']])) {

                // Já existe → soma a quantidade total
                $resultado[$novo['id']]['quantidade'] += $novo['quantidade'];

                // ✅ Soma também a quantidade pendente
                $resultado[$novo['id']]['quantidade_pendente'] += $novo['quantidade_pendente'];

            } else {

                // Não existe → adiciona normalmente
                $resultado[$novo['id']] = $novo;
            }
        }

        return array_values($resultado);
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'id'            => 'nullable|integer',
            'cliente_nome'  => 'required|string|max:255',
            'itens'         => 'required|array|min:1',
            'valor_total'   => 'nullable|numeric|min:0',
        ]);

        // Calcular valor total baseado nas quantidades
        $valorTotalCalculado = collect($validated['itens'])->reduce(function ($carry, $item) {
            return $carry + ($item['preco'] * $item['quantidade']);
        }, 0);

        $valorTotal = $valorTotalCalculado;

        // Se o ID não existir, cria novo pedido
        if (empty($validated['id'])) {
            $pedido = Pedido::create([
                'cliente_nome'      => $validated['cliente_nome'],
                'itens'             => $validated['itens'],
                'valor_total'       => $valorTotal,
                'status'            => 'pendente',
                'status_pagamento'  => 'Não pago',
            ]);

            return response()->json([
                'message' => 'Pedido criado com sucesso!',
                'pedido'  => $pedido,
            ]);
        }

        // Caso contrário, atualiza o pedido existente
        $pedido = Pedido::findOrFail($validated['id']);

        // Mescla os itens existentes com os novos
        $itensMesclados = $this->mergeItens($pedido->itens, $validated['itens']);

        // Recalcula total com itens mesclados
        $novoTotal = collect($itensMesclados)->reduce(function ($carry, $item) {
            return $carry + ($item['preco'] * $item['quantidade']);
        }, 0);

        $pedido->update([
            'cliente_nome'      => $validated['cliente_nome'],
            'itens'             => $itensMesclados,
            'valor_total'       => $novoTotal,
            'status'            => 'pendente',
            'status_pagamento'  => 'Não pago',
        ]);

        return response()->json([
            'message' => 'Pedido atualizado com sucesso!',
            'pedido'  => $pedido,
        ]);
    }

    public function find(string $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido não encontrado.'
            ], 404);
        }

        return response()->json([
            'message' => 'Pedido encontrado com sucesso.',
            'data' => $pedido
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'cliente_nome' => 'required|string|max:255',
            'itens'        => 'required|array|min:1',
            'valor_total'  => 'nullable|numeric|min:0',
        ]);

        $pedido = Pedido::findOrFail($id);

        // Calcula total com base nos itens enviados
        $novoTotal = collect($validated['itens'])->reduce(function ($carry, $item) {
            return $carry + ($item['preco'] * $item['quantidade']);
        }, 0);

        // Atualiza completamente os itens, sem merge
        $pedido->update([
            'cliente_nome' => $validated['cliente_nome'],
            'itens'        => $validated['itens'],  // <-- SUBSTITUI TUDO
            'valor_total'  => $novoTotal,
        ]);

        return response()->json([
            'message' => 'Pedido atualizado com sucesso (substituição completa).',
            'pedido'  => $pedido,
        ]);
    }

    public function imprimirTotalPedido(Pedido $pedido)
    {
        // --- Gera texto do cupom ---
        $texto = $this->gerarTextoCupomPedido($pedido);

        // --- Encoding para impressora ---
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

        Log::info("🧾 Impressão parcial Pedido #{$pedido->id}\n" . $texto);

        try {

            $printerServerUrl = "http://host.docker.internal:8051";

            $payload = [
                'texto'      => $texto,
                'impressora' => '71840',
            ];

            $response = Http::post($printerServerUrl, $payload);

            if ($response->successful()) {
                return response()->json([
                    'message' => 'Cupom parcial impresso com sucesso!',
                ]);
            }

            return response()->json([
                'message' => 'Erro ao imprimir cupom parcial.',
                'error'   => $response->body(),
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Falha ao conectar ao servidor de impressão.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    
    private function gerarTextoCupomPedido(Pedido $pedido): string
    {
        $empresa = config('nfe');

        $texto = "";
        $texto .= "     {$empresa['nome_fantasia']}     \n";
        $texto .= "{$empresa['razao_social']}\n";
        $texto .= "CNPJ: {$empresa['cnpj']}\n";
        $texto .= "{$empresa['municipio']} - {$empresa['uf']}\n";
        $texto .= "--------------------------------\n";
        $texto .= "        NOTA PARCIAL PEDIDO     \n";
        $texto .= "================================\n";

        $texto .= "Pedido Nº: {$pedido->id}\n";
        $texto .= "Cliente: {$pedido->cliente_nome}\n";
        $texto .= "Status: " . strtoupper($pedido->status) . "\n";
        $texto .= "Pagamento: {$pedido->status_pagamento}\n";
        $texto .= "Data: " . $pedido->created_at->format('d/m/Y H:i') . "\n";
        $texto .= "--------------------------------\n";

        $texto .= "ITENS:\n";
        $texto .= "--------------------------------\n";

        foreach ($pedido->itens as $item) {

            $nome  = $item['nome'];
            $qtd   = $item['quantidade'];
            $preco = $item['preco'];

            $totalItem = $qtd * $preco;

            $texto .= "{$nome}\n";
            $texto .= sprintf(
                "  %2dx R$ %6s = R$ %6s\n",
                $qtd,
                number_format($preco, 2, ',', ''),
                number_format($totalItem, 2, ',', '')
            );
        }

        $texto .= "--------------------------------\n";
        $texto .= "SUBTOTAL..............: R$ " . number_format($pedido->valor_total, 2, ',', '') . "\n";
        $texto .= "================================\n";

        $texto .= " * Documento para conferência * \n";
        $texto .= "================================\n\n\n";

        return $texto;
    }

}
