<?php

namespace App\Livewire\Components\Inputs;

use Livewire\Component;

class Number extends Component
{
    public $model = 'T9W16I1KAPLS5BB0';
    public string $length;
    public string $name;
    public string $label;

    public function render(array $params = []): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        $this->model = $params['model'];
        $this->length = $params['length'];
        $this->name = $params['name'];
        $this->label = $params['label'];

        return view('livewire.components.inputs.number');
    }
}
