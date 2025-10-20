@csrf
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-text
        label="Nome"
        name="nome"
        lenght="6/12"
        :value="$cliente->nome ?? old('nome')"
    />
    <x-layouts.inputs.input-normal-text
        label="CPF"
        name="cpf"
        lenght="6/12"
        :value="$cliente->cpf ?? old('cpf')"
    />
</div>
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-date
        type="date"
        label="Data de Nascimento"
        lenght="4/12"
        name="data_nascimento"
        :value="$cliente->data_nascimento ?? old('data_nascimento')"
    />
</div>
<br>
<x-layouts.buttons.submit-button text="Salvar" />

