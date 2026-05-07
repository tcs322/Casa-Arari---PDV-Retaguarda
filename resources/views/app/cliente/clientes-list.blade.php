<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        table td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>

    <h1>Lista de Clientes</h1>

    <div class="info">
        <strong>Data de geração:</strong>
        {{ now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="70%">Nome</th>
                <th width="30%">Telefone</th>
            </tr>
        </thead>

        <tbody>
            @forelse($clientes as $cliente)
                <tr>
                    <td>
                        {{ $cliente->nome }}
                    </td>

                    <td>
                        {{ $cliente->telefone }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="text-align: center;">
                        Nenhum cliente encontrado
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total de clientes: {{ count($clientes) }}
    </div>

</body>
</html>