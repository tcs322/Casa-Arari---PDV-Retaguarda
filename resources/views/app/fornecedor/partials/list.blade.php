<x-layouts.tables.simple-table
    :headers="[
        'Documento',
        'Razão Social',
        'Nome Fantasia',
        'Tipo',
        'Data cadastro',
        'Última atualização',
        'Opções'
    ]"
    :paginator="$fornecedores"
    :appends="$filters"
>
@section('table-content')
    @foreach($fornecedores->items() as $index => $fornecedor)
        <tr>
            <td>{{$fornecedor->documento}}</td>
            <td>{{$fornecedor->razao_social}}</td>
            <td>{{$fornecedor->nome_fantasia}}</td>
            <td>{{$fornecedor->tipo}}</td>
            <td>{{$fornecedor->created_at_for_humans}}</td>
            <td>{{$fornecedor->updated_at_for_humans}}</td>
            <td class="text-right">
                <x-layouts.buttons.action-button
                    text="Ver"
                    action="ver"
                    color="secondary"
                    :route="route('fornecedor.show', $fornecedor->uuid)"/>
                <x-layouts.buttons.action-button
                    text="Editar"
                    action="editar"
                    color="primary"
                    :route="route('fornecedor.edit', $fornecedor->uuid)"/>
            </td>
        </tr>
    @endforeach
@endsection
</x-layouts.tables.simple-table>
