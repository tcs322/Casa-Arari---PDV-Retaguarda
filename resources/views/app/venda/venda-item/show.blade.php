@extends('app.layouts.app')

@section('content')
<div class="container mt-4">

    <h2>Relatório de Produtos Vendidos</h2>

    <p class="text-muted">
        Período: <strong>{{ $data_inicio }}</strong> até <strong>{{ $data_fim }}</strong><br>
        Tipo: <strong>{{ $tipo ?? 'Todos' }}</strong>
    </p>

    <a
        href="{{ route('venda_itens.by_period_pdf', [
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'tipo' => $tipo
        ]) }}"
        class="btn mb-3"
        target="_blank"
        style="
            background-color: #922B21;
            border-color: #922B21;
            color: white;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 16px;
            display: inline-block;
        "
        onmouseover="this.style.backgroundColor='#A93226'"
        onmouseout="this.style.backgroundColor='#922B21'"
    >
        Exportar PDF
    </a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade Vendida</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>

            @forelse ($agrupado as $produto)
                <tr>
                    <td>{{ $produto['produto'] }}</td>
                    <td>{{ $produto['quantidade_total'] }}</td>
                    <td>{{ $produto['tipo'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Nenhum item encontrado.</td>
                </tr>
            @endforelse

        </tbody>
    </table>

    <a 
        href="{{ route('venda_itens.search') }}" 
        class="btn mt-3"
        style="
            background-color: #145A32;
            border-color: #145A32;
            color: white;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 16px;
            display: inline-block;
        "
        onmouseover="this.style.backgroundColor='#1D8348'"
        onmouseout="this.style.backgroundColor='#145A32'"
    >
        Nova Busca
    </a>
</div>
@endsection
