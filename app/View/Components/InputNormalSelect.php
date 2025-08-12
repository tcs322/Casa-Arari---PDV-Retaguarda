<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InputNormalSelect extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        protected string $lenght,
        protected string $name,
        protected string $label,
        protected string $origin,
        protected array $data,
        protected string $value
    ) {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {

        switch ($this->origin) {
            case 'fornecedores':
                // Lógica para buscar dados de fornecedores.
                $data = [
                    ['value' => 'fornecedor1', 'text' => 'Fornecedor 1'],
                    ['value' => 'fornecedor2', 'text' => 'Fornecedor 2'],
                ];
                break;

            case 'portes':
                // Lógica para buscar dados de portes.
                $data = [
                    ['value' => 'porte1', 'text' => 'Porte 1'],
                    ['value' => 'porte2', 'text' => 'Porte 2'],
                ];
                break;

            default:
                // Lógica padrão se a origem não for reconhecida.
                $data = $this->data;
                break;
        }

        return view('components.app.input-normal-select', [
            "lenght" => $this->lenght,
            "name" => $this->name,
            "label" => $this->label,
            "origin" => $this->origin,
            "data" => $this->data,
            "value" => $this->value
        ]);
    }
}
