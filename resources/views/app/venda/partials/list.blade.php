<x-layouts.tables.simple-table
    :headers="[
        'Número',
        'Fornecedor',
        'Valor total',
        'Data cadastro',
        'Opções'
    ]"
    :paginator="$notas"
    :appends="$filters"
>
@section('table-content')
    @foreach($notas->items() as $index => $nota)
        <tr>
            <td>{{$nota->numero_nota}}</td>
            <td>{{$nota->fornecedor['nome_fantasia']}}</td>
            <td>{{$nota->valor_total}}</td>
            <td>{{$nota->created_at_for_humans}}</td>
            <td class="text-right">
                <x-layouts.buttons.action-button
                    text="Ver"
                    action="ver"
                    color="secondary"
                    :route="route('fornecedor.show', $nota->uuid)"/>
                <x-layouts.buttons.action-button
                    text="Editar"
                    action="editar"
                    color="primary"
                    :route="route('fornecedor.edit', $nota->uuid)"/>
            </td>
        </tr>
    @endforeach
@endsection
</x-layouts.tables.simple-table>
