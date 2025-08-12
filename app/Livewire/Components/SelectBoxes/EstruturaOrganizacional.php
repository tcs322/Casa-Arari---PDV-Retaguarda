<?php

namespace App\Livewire\Components\SelectBoxes;

use App\Models\Departamento;
use App\Models\PostoTrabalho;
use App\Models\Setor;
use App\Repositories\Departamento\DepartamentoEloquentRepository;
use App\Repositories\PostoTrabalho\PostoTrabalhoEloquentRepository;
use App\Repositories\Setor\SetorEloquentRepository;
use Livewire\Component;

class EstruturaOrganizacional extends Component
{
    public $components;
    public $postoTrabalhoUuid;
    public $setorUuid;
    public $departamentoUuid;

    public $setores = [];
    public $departamentos = [];

    public $loading = true;


    public function mount(
        string $postoTrabalhoUuid = null,
        string $setorUuid = null,
        string $departamentoUuid = null) : void
    {
        $this->postoTrabalhoUuid = $postoTrabalhoUuid;
        $this->setorUuid = $setorUuid;
        $this->departamentoUuid = $departamentoUuid;
    }

    public function render()
    {
        $postoTrabalhoRepository = new PostoTrabalhoEloquentRepository(new PostoTrabalho());
        if(!is_null($this->postoTrabalhoUuid)) {
            $this->selecionaPostoTrabalho();
            $this->selecionaSetor();
        }
        return view('livewire.components.select-boxes.estrutura-organizacional', [
            'postosTrabalho' => $postoTrabalhoRepository->all(),
        ]);
    }

    public function selecionaPostoTrabalho() : void
    {
        $this->setores = [];
        $this->departamentos = [];
        if(!empty($this->postoTrabalhoUuid)) {
            $setorRespoitory = new SetorEloquentRepository(new Setor());
            $this->setores = $setorRespoitory->allByPostoTrabalho($this->postoTrabalhoUuid);
        }
    }

    public function selecionaSetor() : void
    {
        $this->departamentos = [];
        if(!empty($this->setorUuid)) {
            $departamentoRepository = new DepartamentoEloquentRepository(new Departamento());
            $this->departamentos = $departamentoRepository->allBySetor($this->setorUuid);
        }
    }
}
