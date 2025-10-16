<form class="mt-2" action="{{ route('usuario.index') }}">
    <x-layouts.inputs.input-search-list :filters="$filters" />
</form>
