<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $pedido->update(['status' => 'preparado']);

        return response()->json([
            'message' => 'Pedido marcado como preparado!',
        ]);
    }

    public function allPedidos()
    {
        return Pedido::all();
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
                // Já existe → soma a quantidade
                $resultado[$novo['id']]['quantidade'] += $novo['quantidade'];
            } else {
                // Não existe → adiciona
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
                'cliente_nome' => $validated['cliente_nome'],
                'itens'        => $validated['itens'],
                'valor_total'  => $valorTotal,
                'status'       => 'pendente'
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
            'cliente_nome' => $validated['cliente_nome'],
            'itens'        => $itensMesclados,
            'valor_total'  => $novoTotal,
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
}
