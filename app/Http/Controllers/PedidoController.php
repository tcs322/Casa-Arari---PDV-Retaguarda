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
        $validated = $request->validate([
            'cliente_nome' => 'required|string|max:255',
            'itens' => 'required|array|min:1',
        ]);

        $pedido = Pedido::create([
            'cliente_nome' => $validated['cliente_nome'],
            'itens' => $validated['itens'],
            'status' => 'pendente',
        ]);

        return response()->json([
            'message' => 'Pedido criado com sucesso!',
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
