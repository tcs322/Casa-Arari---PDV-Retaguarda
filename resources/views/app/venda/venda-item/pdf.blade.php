<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { margin-bottom: 10px; }
        .meta { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Relatório de Produtos Vendidos</h2>
        <div class="meta">
            <div>Período: <strong>{{ $data_inicio }}</strong> até <strong>{{ $data_fim }}</strong></div>
            <div>Tipo: <strong>{{ $tipo ?? 'Todos' }}</strong></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade Vendida</th>
                <th>Tipo</th>
                <th class="text-right">Subtotal (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($agrupado as $produto)
                <tr>
                    <td>{{ $produto['produto'] }}</td>
                    <td>{{ $produto['quantidade_total'] }}</td>
                    <td>{{ $produto['tipo'] }}</td>
                    <td class="text-right">
                        {{ number_format($produto['subtotal_total'], 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">Valor Total (R$)</td>
                <td class="text-right">{{ number_format($totalGeral, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
