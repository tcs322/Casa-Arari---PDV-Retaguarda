@csrf
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-text
        label="Razão social"
        name="razao_social"
        lenght="6/12"
        :value="$fornecedor->razao_social ?? old('razao_social')"
    />
    <x-layouts.inputs.input-normal-text
        label="Nome fantasia"
        name="nome_fantasia"
        lenght="6/12"
        :value="$fornecedor->nome_fantasia ?? old('nome_fantasia')"
    />
</div>
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-select-enum
        label="Tipo de fornecedor"
        name="tipo"
        origin="tipo"
        lenght="4/12"
        :data="$formData['tipo']"
        :value="$fornecedor->tipo ?? old('tipo')"
    />
    <x-layouts.inputs.input-normal-select-enum
        label="Tipo Documento"
        name="tipo_documento"
        origin="tipo_documento"
        lenght="4/12"
        :data="$formData['tipo_documento']"
        :value="$fornecedor->tipo_documento ?? old('tipo_documento')"
    />
    <x-layouts.inputs.input-normal-text
        label="Documento"
        name="documento"
        lenght="4/12"
        :value="$fornecedor->documento ?? old('documento')"
    />
</div>

<x-layouts.buttons.submit-button text="Salvar"  />
