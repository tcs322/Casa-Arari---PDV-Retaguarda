<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        h2 { margin-bottom: 5px; }
    </style>
</head>
<body>

<h2>Relatório de Produtos Vendidos</h2>

<p>
    Período: <strong>{{ $data_inicio }}</strong> até <strong>{{ $data_fim }}</strong><br>
    Tipo: <strong>{{ $tipo ?? 'Todos' }}</strong>
</p>

<table>
    <thead>
        <tr>
            <th>Produto</th>
            <th>Quantidade Vendida</th>
            <th>Tipo</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($agrupado as $produto)
            <tr>
                <td>{{ $produto['produto'] }}</td>
                <td>{{ $produto['quantidade_total'] }}</td>
                <td>{{ $produto['tipo'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
