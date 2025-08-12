@csrf

<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-text
        label="Código"
        name="codigo"
        lenght="4/12"
        :value="$product->codigo ?? old('codigo')"
    />
    <x-layouts.inputs.input-normal-text
        label="Nome/Título"
        name="nome_titulo"
        lenght="6/12"
        :value="$product->nome ?? old('nome')"
    />
</div>
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-text
        label="Preço"
        name="preco"
        lenght="4/12"
        :value="$product->preco ?? old('preco')"
    />
    <x-layouts.inputs.input-normal-number
        label="Qtd Em Estoque"
        name="estoque"
        lenght="4/12"
        :value="$product->estoque ?? old('estoque')"
    />
</div>
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-text
        label="Autor"
        name="autor"
        lenght="4/12"
        :value="$product->autor ?? old('autor')"
    />
    <x-layouts.inputs.input-normal-number
        label="Edição"
        name="edicao"
        lenght="4/12"
        :value="$product->edicao ?? old('edicao')"
    />
</div>
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-select
        :data="$formData['fornecedores']"
        label="Fornecedor/Editora"
        name="fornecedor_uuid"
        lenght="8/12"
        labelKey="razao_social"
        :value="$product->fornecedor_uuid ?? old('fornecedor_uuid')"
    />
</div>
    
<x-layouts.buttons.submit-button text="Salvar"/>
