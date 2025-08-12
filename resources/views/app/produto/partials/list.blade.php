<x-layouts.tables.simple-table
    :headers="[
        'Nome',
        'Descricao',
        'Tipo',
        'Ações'
    ]"
    :paginator="$produtos"
    :appends="$filters"
>
    @section('table-content')
        @foreach($produtos->items() as $index => $produto)
            <tr>
                <td>{{ $produto->nome }}</td>
                <td>{{ $produto->descricao }}</td>
                <td>{{ $produto->tipo }}</td>
                <td class="text-right">
                <x-layouts.buttons.action-button
                    text="Ver"
                    action="ver"
                    color="secondary"
                    :route="route('cargo.show', $produto->uuid)"/>
                <x-layouts.buttons.action-button
                    text="Editar"
                    action="editar"
                    color="primary"
                    :route="route('promotor.edit', $produto->uuid)"/>
                <x-layouts.buttons.action-button
                    text="Excluir"
                    action="excluir"
                    color="danger"
                    :identificador="'drawer-delete-confirmacao'"
                    :route="route('promotor.delete', [
                        'uuid' => $produto->uuid
                    ])"
                />
                </td>
            </tr>
        @endforeach
    @endsection
</x-layouts.tables.simple-table>
