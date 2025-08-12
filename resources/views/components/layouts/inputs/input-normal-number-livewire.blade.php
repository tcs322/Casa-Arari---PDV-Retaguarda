<div class="w-full md:w-{{$lenght}} px-3 mb-6 md:mb-0">
    <label class="block uppercase tracking-wide text-xs font-bold mb-2" for="grid-city">
        {{$label}}
    </label>
    <input {{$readonly ?? false}} type="number" min="0" wire:model="{{$model}}" wire:change="{{$change}}" wire:blur="{{$blur}}" value="{{$value ?? 0}}" name="{{$name}}" id="{{$name}}" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
    @error($name)
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>
