<x-layouts.tables.simple-table
    :headers="[
        'Nome',
        'Data De Nascimento',
        'Opções'
    ]"
    :paginator="$clientes"
    :appends="$filters"
>
@section('table-content')
    @foreach($clientes->items() as $index => $cliente)
        <tr>
            <td>{{$cliente->nome}}</td>
            <td>{{$cliente->data_nascimento}}</td>
            <td class="text-right">
                <x-layouts.buttons.action-button
                    text="Ver"
                    action="ver"
                    color="secondary"
                    :route="route('cliente.show', $cliente->uuid)"/>
                <x-layouts.buttons.action-button
                    text="Editar"
                    action="editar"
                    color="primary"
                    :route="route('cliente.edit', $cliente->uuid)"/>
            </td>
        </tr>
    @endforeach
@endsection
</x-layouts.tables.simple-table>
