<div class="w-full md:w-{{$lenght}} px-3 mb-6 md:mb-0">
    <label class="block uppercase tracking-wide text-xs font-bold mb-2" for="{{ $name }}">
        {{ $label }}
    </label>
    <input
        wire:model="{{$model}}" wire:change="{{$change}}" wire:blur="{{$blur}}"
        type="date"
        name="{{ $name }}"
        class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
        value="{{ $value }}"
    />
    @error($name)
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>
