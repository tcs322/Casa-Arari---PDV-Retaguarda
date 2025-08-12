<div class="flex flex-wrap -mx-3 mb-2 mt-4">
    @if(in_array('postos_trabalho', $components))
        <x-layouts.inputs.input-normal-select-livewire
            label="Posto de Trabalho"
            name="postos_trabalho_uuid"
            :value="$postoTrabalhoUuid"
            lenght="4/12"
            change="selecionaPostoTrabalho"
            model="postoTrabalhoUuid"
            :data="$postosTrabalho"
        ></x-layouts.inputs.input-normal-select-livewire>
    @endif
    @if(in_array('setores', $components))
        <x-layouts.inputs.input-normal-select-livewire
            label="Setor"
            name="setores_uuid"
            :value="$setorUuid"
            lenght="4/12"
            change="selecionaSetor"
            model="setorUuid"
            :data="$setores"
        ></x-layouts.inputs.input-normal-select-livewire>
    @endif
    @if(in_array('departamentos', $components))
        <x-layouts.inputs.input-normal-select-livewire
            label="Departamento"
            name="departamentos_uuid"
            :value="$departamentoUuid"
            lenght="4/12"
            change=""
            model="departamentoUuid"
            :data="$departamentos"
        ></x-layouts.inputs.input-normal-select-livewire>
    @endif
</div>
