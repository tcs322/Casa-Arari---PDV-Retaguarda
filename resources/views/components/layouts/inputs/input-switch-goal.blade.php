<div>
    <label class="block uppercase tracking-wide text-xs font-bold mt-3 mb-2" for="{{ $name }}">
        {{ $label }}
    </label>
@php
    $checked = '';
    if (isset($value) && !is_null($value)) {
        if ($value === App\Enums\FinalidadeModeloEnum::ESTAGIO_PROBATORIO) {
            $checked = 'checked="checked"';
        }
    }
@endphp
    <input
        type="radio"
        name="{{$name}}"
        {{$checked}}
        value="{{ App\Enums\FinalidadeModeloEnum::ESTAGIO_PROBATORIO }}"
    > {{App\Enums\FinalidadeModeloEnum::getKey(App\Enums\FinalidadeModeloEnum::ESTAGIO_PROBATORIO)}}

@php
    $checked = '';
    if (isset($value) && !is_null($value)) {
        if ($value === App\Enums\FinalidadeModeloEnum::EVOLUCAO_FUNCIONAL) {
            $checked = 'checked="checked"';
        }
    }
@endphp
    <input
        type="radio"
        name="{{$name}}"
        {{$checked}}
        value="{{ App\Enums\FinalidadeModeloEnum::EVOLUCAO_FUNCIONAL }}"
    > {{App\Enums\FinalidadeModeloEnum::getKey(App\Enums\FinalidadeModeloEnum::EVOLUCAO_FUNCIONAL)}}
    @error($name)
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>



