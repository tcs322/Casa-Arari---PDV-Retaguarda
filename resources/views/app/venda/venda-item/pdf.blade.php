<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { margin-bottom: 15px; }
        .meta { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; }
        .fornecedor-title { font-size: 14px; font-weight: bold; margin-top: 25px; }
        .sub-footer { font-weight: bold; background: #e8e8e8; }
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

    @foreach ($agrupadoPorFornecedor as $fornecedor => $dados)

        <div class="fornecedor-title">
            Fornecedor: {{ $fornecedor }}
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
                @foreach ($dados['produtos'] as $produto)
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

            <!-- Quantidade total do fornecedor -->
            <tfoot>
                <tr class="sub-footer">
                    <td colspan="3" class="text-right">Quantidade total do fornecedor</td>
                    <td class="text-right">{{ $dados['quantidade_total_fornecedor'] }}</td>
                </tr>
            </tfoot>
        </table>

    @endforeach

    <!-- Total Geral -->
    <table style="margin-top: 30px;">
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">Valor Total Geral (R$)</td>
                <td class="text-right">
                    {{ number_format($totalGeral, 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
