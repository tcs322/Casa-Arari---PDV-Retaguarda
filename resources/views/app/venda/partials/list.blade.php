<x-layouts.tables.simple-table
    :headers="[
        'Número Nfe',
        'Forma de Pagamento',
        'Valor total',
        'Data',
        'Opções'
    ]"
    :paginator="$vendas"
    :appends="$filters"
>
@section('table-content')
    @foreach($vendas->items() as $index => $venda)
        <tr>
            <td>{{$venda->numero_nota_fiscal}}</td>
            <td>{{$venda->forma_pagamento}}</td>
            <td>{{$venda->valor_total}}</td>
            <td>{{$venda->created_at_for_humans}}</td>
            <td class="text-right">
                <x-layouts.buttons.action-button
                    text="Ver"
                    action="ver"
                    color="secondary"
                    :route="route('fornecedor.show', $venda->uuid)"/>
                <x-layouts.buttons.action-button
                    text="Editar"
                    action="editar"
                    color="primary"
                    :route="route('fornecedor.edit', $venda->uuid)"/>
            </td>
        </tr>
    @endforeach
@endsection
</x-layouts.tables.simple-table>
