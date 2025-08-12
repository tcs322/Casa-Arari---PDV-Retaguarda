<div>
    <label class="block uppercase tracking-wide text-xs font-bold mb-2" for="{{ $name }}">
        {{ $label }}
    </label>
@php
    $checked = '';
    if (isset($value) && !is_null($value)) {
        if ($value === App\Enums\SituacaoUsuarioEnum::ATIVO) {
            $checked = 'checked="checked"';
        }
    }
@endphp
    <input
        type="radio"
        name="{{$name}}"
        {{$checked}}
        value="{{ App\Enums\SituacaoUsuarioEnum::ATIVO }}"
    > {{App\Enums\SituacaoUsuarioEnum::getKey(App\Enums\SituacaoUsuarioEnum::ATIVO)}}

@php
    $checked = '';
    if (isset($value) && !is_null($value)) {
        if ($value === App\Enums\SituacaoUsuarioEnum::INATIVO) {
            $checked = 'checked="checked"';
        }
    }
@endphp
    <input
        type="radio"
        name="{{$name}}"
        {{$checked}}
        value="{{ App\Enums\SituacaoUsuarioEnum::INATIVO }}"
    > {{App\Enums\SituacaoUsuarioEnum::getKey(App\Enums\SituacaoUsuarioEnum::INATIVO)}}

    @error($name)
        <br>
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>


