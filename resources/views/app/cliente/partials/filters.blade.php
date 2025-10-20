<form class="mt-2" action="{{ route('cliente.index') }}">
    <x-layouts.inputs.input-search-list :filters="$filters" />
</form>
