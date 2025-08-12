@csrf

<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-text
        label="Produto"
        name="nome"
        lenght="8/12"
        :value="$produto->nome ?? old('nome')"
    />
</div>
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-text
        label="Descricao"
        name="descricao"
        lenght="8/12"
        :value="$produto->descricao ?? old('descricao')"
    />
</div>
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-number
        label="Peso"
        name="peso"
        lenght="4/12"
        :value="$produto->peso ?? old('peso')"
    />
    <x-layouts.inputs.input-normal-select-enum
        label="Tipo"
        name="tipo"
        origin="tipo"
        lenght="4/12"
        :data="$formData['tipo']"
        :value="$produto->tipo ?? old('tipo')"
    />
</div>
    
<x-layouts.buttons.submit-button text="Salvar"/>
