<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin-bottom: 8px; }
        .meta { font-size: 11px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; }
    </style>
</head>
<body>

    <h2>Relatório de Vendas</h2>

    <div class="meta">
        <div>Período: <strong>{{ $data_inicio }}</strong> até <strong>{{ $data_fim }}</strong></div>
        @if($usuario_uuid)
            <div>Usuário: <strong>{{ $usuario_uuid }}</strong></div>
        @endif
        @if($forma_pagamento)
            <div>Forma de Pagamento: <strong>{{ $forma_pagamento }}</strong></div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>UUID / Nº Nota</th>
                <th>Data</th>
                <th>Usuário</th>
                <th>Forma Pagamento</th>
                <th class="text-right">Subtotal (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $v)
                <tr>
                    <td>{{ $v->uuid }} @if($v->numero_nota_fiscal) - NF: {{ $v->numero_nota_fiscal }} @endif</td>
                    <td>{{ optional($v->data_venda)->format('d/m/Y H:i') }}</td>
                    <td>{{ $v->usuario->name ?? $v->usuario->nome ?? '-' }}</td>
                    <td>{{ $v->forma_pagamento }}</td>
                    <td class="text-right">{{ number_format($v->calculated_subtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">Valor Total (R$)</td>
                <td class="text-right">{{ number_format($totalGeral, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
