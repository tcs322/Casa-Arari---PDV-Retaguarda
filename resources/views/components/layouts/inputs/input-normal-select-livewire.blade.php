<div class="w-full md:w-{{$lenght}} px-3 mb-6 md:mb-0">
    <label class="block uppercase tracking-wide text-xs font-bold mb-2" for="{{$name}}">
        {{$label}}
    </label>
    <div class="relative">
        <select wire:model="{{$model}}" wire:change="{{$change}}" class="block appearance-none w-full bg-gray-200 border border-gray-200 text-black py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="{{$name}}" name="{{$name}}">
            <option value="">Selecione</option>
            @foreach($data as $item)
                @if (isset($value) && !is_null($value))
                    @php
                        $selected= '';
                            if ($item['uuid'] == $value) {
                               $selected = "selected";
                            }
                    @endphp
                    <option {{$selected}} value="{{ $item['uuid'] }}">{{ $item['nome'] ?? $item['descricao'] }}</option>
                @else
                    <option value="{{ $item['uuid'] }}">{{ $item['nome']  ?? $item['descricao']}}</option>
                @endif
            @endforeach
        </select>
        @error($name)
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>
</div>
