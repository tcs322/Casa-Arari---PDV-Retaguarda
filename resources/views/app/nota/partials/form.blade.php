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
        :value="$nota->valor_total ?? old('valor_total')"
    />
</div>
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-select-enum
        label="Tipo de Nota"
        name="tipo_nota"
        origin="tipo_nota"
        lenght="4/12"
        :data="$formData['tipo_nota']"
        :value="$nota->tipo_nota ?? old('tipo_nota')"
    />
    <x-layouts.inputs.input-normal-select
        :data="$formData['fornecedores']"
        label="Fornecedor/Editora"
        name="fornecedor_uuid"
        lenght="8/12"
        labelKey="razao_social"
        :value="$nota->fornecedor_uuid ?? old('fornecedor_uuid')"
    />
</div>

<x-layouts.buttons.submit-button text="Salvar"  />
