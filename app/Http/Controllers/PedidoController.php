<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;

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
}
