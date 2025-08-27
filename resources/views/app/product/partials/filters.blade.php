<form class="mt-2" action="{{ route('produto.index') }}">
    <x-layouts.inputs.input-search-list :filters="$filters" />
</form>
