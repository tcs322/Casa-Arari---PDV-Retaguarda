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

        $agrupado = $vendaItens->groupBy('produto_uuid')->map(function ($items) {
            return [
                'produto' => $items->first()->produto->nome_titulo ?? 'Produto removido',
                'quantidade_total' => $items->sum('quantidade'),
                'tipo' => $items->first()->produto->tipo ?? null,
            ];
        });

        return view('app.venda.venda-item.show', [
            'agrupado' => $agrupado,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'tipo' => $request->tipo
        ]);
    }

    public function exportPdf(Request $request)
    {
        // Mesma busca feita no getByPeriod
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

        $agrupado = $vendaItens->groupBy('produto_uuid')->map(function ($items) {
            return [
                'produto' => $items->first()->produto->nome_titulo ?? 'Produto removido',
                'quantidade_total' => $items->sum('quantidade'),
                'tipo' => $items->first()->produto->tipo ?? null,
            ];
        });

        $pdf = Pdf::loadView('app.venda.venda-item.pdf', [
            'agrupado' => $agrupado,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'tipo' => $request->tipo
        ]);

        return $pdf->download('relatorio-produtos-vendidos.pdf');
    }
}
