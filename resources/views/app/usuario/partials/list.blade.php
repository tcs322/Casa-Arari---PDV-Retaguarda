<x-layouts.tables.simple-table
    :headers="[
        'Nome',
        'E-mail',
        'Situação',
        'Opções'
    ]"
    :paginator="$user"
    :appends="$filters"
>
@section('table-content')
    @foreach($user->items() as $index => $users)
        <tr>
            <td>{{$users->name}}</td>
            <td>{{$users->email}}</td>
            <td><x-layouts.badges.situacao-usuario
                :situacao="$users->situacao"
                /></td>
            <td class="text-right">
                <x-layouts.buttons.action-button
                    text="Ver"
                    action="ver"
                    color="secondary"
                    :route="route('usuario.show', $users->uuid)"/>
                <x-layouts.buttons.action-button
                    text="Editar"
                    action="editar"
                    color="primary"
                    :route="route('usuario.edit', $users->uuid)"/>
                <x-layouts.buttons.resetar-senha-button
                    :route="route('usuario.reset', $users->uuid)"
                    identificador="resetar-senha-{{ $users->uuid }}"
                />
            </td>
        </tr>
    @endforeach
@endsection
</x-layouts.tables.simple-table>
