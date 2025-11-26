@extends('app.layouts.app')

@section('content')
<div class="container mt-4 p-4">

    <h2 class="mb-4">Vendas</h2>

    <p class="text-muted mb-4">
        Período: <strong>{{ $data_inicio }}</strong> até <strong>{{ $data_fim }}</strong>
    </p>

    <a href="{{ route('vendas.by_period_pdf', [
        'data_inicio' => $data_inicio,
        'data_fim' => $data_fim,
        'usuario_uuid' => $usuario_uuid,
        'forma_pagamento' => $forma_pagamento
    ]) }}"
       target="_blank"
       class="btn btn-danger mb-6"
       style="background-color: #922B21; border-color: #922B21; color: white; border-radius:6px; padding:12px 18px; font-size:16px;"
       onmouseover="this.style.backgroundColor='#A93226'"
       onmouseout="this.style.backgroundColor='#922B21'">
        Exportar PDF
    </a>

    <table class="table table-bordered table-striped mt-4 mb-4">
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
            @forelse($vendas as $v)
                <tr>
                    <td>{{ $v->uuid }} @if($v->numero_nota_fiscal) - NF: {{ $v->numero_nota_fiscal }}@endif</td>
                    <td>{{ optional($v->data_venda)->format('d/m/Y H:i') }}</td>
                    <td>{{ $v->usuario->name ?? $v->usuario->nome ?? '-' }}</td>
                    <td>{{ $v->forma_pagamento }}</td>
                    <td class="text-right">{{ number_format($v->calculated_subtotal, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-4">Nenhuma venda encontrada.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="font-weight-bold">
                <td colspan="4" class="text-right">Valor Total (R$)</td>
                <td class="text-right">{{ number_format($totalGeral, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <a href="{{ route('vendas.search') }}"
       class="btn mt-4"
       style="background-color:#616A6B; border-color:#616A6B; color:#fff; border-radius:6px; padding:12px 18px; font-size:16px;"
       onmouseover="this.style.backgroundColor='#7B7D7D'"
       onmouseout="this.style.backgroundColor='#616A6B'">
       Nova Busca
    </a>

</div>
@endsection
