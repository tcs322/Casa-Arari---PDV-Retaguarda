<x-layouts.tables.simple-table
    :headers="[
        'Nome',
        'Preço',
        'Qtd Em Estoque',
        'Ações'
    ]"
    :paginator="$products"
    :appends="$filters"
>
    @section('table-content')
        @foreach($products->items() as $index => $product)
            <tr>
                <td>{{ $product->nome_titulo }}</td>
                <td>{{ $product->preco }}</td>
                <td>{{ $product->estoque }}</td>
                <td class="text-right">
                <x-layouts.buttons.action-button
                    text="Ver"
                    action="ver"
                    color="secondary"
                    :route="route('fornecedor.show', $product->uuid)"/>
                <x-layouts.buttons.action-button
                    text="Editar"
                    action="editar"
                    color="primary"
                    :route="route('fornecedor.edit', $product->uuid)"/>
                <x-layouts.buttons.action-button
                    text="Excluir"
                    action="excluir"
                    color="danger"
                    :identificador="'drawer-delete-confirmacao'"
                    :route="route('fornecedor.edit', [
                        'uuid' => $product->uuid
                    ])"
                />
                </td>
            </tr>
        @endforeach
    @endsection
</x-layouts.tables.simple-table>
