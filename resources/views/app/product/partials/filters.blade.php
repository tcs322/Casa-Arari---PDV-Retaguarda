<form class="mt-2" action="{{ route('product.index') }}">
    <x-layouts.inputs.input-search-list :filters="$filters" />
</form>
