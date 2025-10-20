@csrf
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-text
        label="Número NFE"
        name="numero_nota"
        lenght="6/12"
        :value="$nota->numero_nota ?? old('numero_nota')"
    />
    <x-layouts.inputs.input-normal-text
        label="Valor Total"
        name="valor_total"
        lenght="6/12"
        :value="$nota->numero_nota ?? old('numero_nota')"
    />
</div>
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-select-enum
        label="Tipo de Nota"
        name="tipo"
        origin="tipo"
        lenght="4/12"
        :data="$formData['tipo_nota']"
        :value="$nota->tipo_nota ?? old('tipo_nota')"
    />
</div>

<x-layouts.buttons.submit-button text="Salvar"  />
