<?php

namespace App\Http\Controllers\App\VendaItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendaItem;
use Barryvdh\DomPDF\Facade\Pdf;

class VendaItemController extends Controller
{
    public function search()
    {
        return view('app.venda.venda-item.search');
    }

    public function getByPeriod(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'tipo' => 'nullable|string|in:LIVRARIA,CAFETERIA'
        ]);

        $dataInicio = $request->data_inicio . ' 00:00:00';
        $dataFim = $request->data_fim . ' 23:59:59';

        $vendaItens = VendaItem::whereHas('venda', function ($query) use ($dataInicio, $dataFim) {
                $query->whereBetween('created_at', [$dataInicio, $dataFim]);
            })
            ->when($request->tipo, function ($query) use ($request) {
                $query->whereHas('produto', function ($q) use ($request) {
                    $q->where('tipo', $request->tipo);
                });
            })
            ->with('produto')
            ->get();

        // Agrupar por produto_uuid
        $agrupado = $vendaItens->groupBy('produto_uuid')->map(function ($items) {
            return [
                'produto' => $items->first()->produto->nome_titulo ?? 'Produto removido',
                'quantidade_total' => $items->sum('quantidade'),
                // soma dos subtotais *registro a registro* para esse produto
                'subtotal_total' => $items->sum(function ($i) {
                    return (float) $i->subtotal;
                }),
                'tipo' => $items->first()->produto->tipo ?? null,
            ];
        });

        // total geral (soma de todos os subtotais dos registros retornados)
        $totalGeral = $vendaItens->sum(function ($i) {
            return (float) $i->subtotal;
        });

        return view('app.venda.venda-item.show', [
            'agrupado' => $agrupado,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'tipo' => $request->tipo,
            'totalGeral' => $totalGeral,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'tipo' => 'nullable|string|in:LIVRARIA,CAFETERIA'
        ]);

        $dataInicio = $request->data_inicio . ' 00:00:00';
        $dataFim = $request->data_fim . ' 23:59:59';

        $vendaItens = VendaItem::whereHas('venda', function ($query) use ($dataInicio, $dataFim) {
                $query->whereBetween('created_at', [$dataInicio, $dataFim]);
            })
            ->when($request->tipo, function ($query) use ($request) {
                $query->whereHas('produto', function ($q) use ($request) {
                    $q->where('tipo', $request->tipo);
                });
            })
            ->with(['produto.fornecedor'])
            ->get();

        // AGRUPAR POR FORNECEDOR
        $agrupadoPorFornecedor = $vendaItens
        ->groupBy(function ($item) {
            $fornecedor = $item->produto->fornecedor->razao_social ?? null;

            // Se estiver vazio, null ou string vazia → agrupa em "Sem fornecedor"
            if (!$fornecedor || trim($fornecedor) === '') {
                return 'Sem fornecedor';
            }

            return $fornecedor;
        })
        ->map(function ($itemsFornecedor) {
            // Agrupa produtos dentro do fornecedor
            $produtos = $itemsFornecedor->groupBy('produto_uuid')->map(function ($itemsProduto) {
                return [
                    'produto' => $itemsProduto->first()->produto->nome_titulo ?? 'Produto removido',
                    'quantidade_total' => $itemsProduto->sum('quantidade'),
                    'subtotal_total' => $itemsProduto->sum(fn ($i) => (float) $i->subtotal),
                    'tipo' => $itemsProduto->first()->produto->tipo ?? null,
                ];
            });

            return [
                'produtos' => $produtos,
                'quantidade_total_fornecedor' => $itemsFornecedor->sum('quantidade')
            ];
        });

        // Total Geral
        $totalGeral = $vendaItens->sum(fn ($i) => (float) $i->subtotal);

        $pdf = Pdf::loadView('app.venda.venda-item.pdf', [
            'agrupadoPorFornecedor' => $agrupadoPorFornecedor,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'tipo' => $request->tipo,
            'totalGeral' => $totalGeral,
        ]);

        return $pdf->download('relatorio-produtos-vendidos.pdf');
    }
}
