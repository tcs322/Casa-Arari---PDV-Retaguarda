@csrf
<div class="flex flex-wrap -mx-3 mb-2">
    <x-layouts.inputs.input-normal-text
        label="Nome"
        name="name"
        lenght="6/12"
        :value="$user->name ?? old('name')"
    />
    <x-layouts.inputs.input-normal-text
        label="Email"
        name="email"
        lenght="6/12"
        :value="$user->email ?? old('email')"
    />
</div>
    <x-layouts.inputs.input-switch
        label="Situação"
        name="situacao"
        :value="$user->situacao ?? old('situacao')"
    />
<br>
<x-layouts.buttons.submit-button text="Salvar" />

