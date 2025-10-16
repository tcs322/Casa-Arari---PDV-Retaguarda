<?php

namespace App\Http\Controllers\App\Cliente;

use App\Actions\Cliente\ClienteAction;
use App\DTO\Cliente\ClienteStoreDTO;
use App\Http\Requests\App\Cliente\ClienteStoreRequest;

class ClienteController
{
    public function __construct(
        protected ClienteAction $action
    ) {}

    public function create()
    {
        $formData = [];

        return view('app.cliente.create', compact('formData'));
    }

    public function store(ClienteStoreRequest $request)
    {
        $this->action->store(ClienteStoreDTO::makeFromRequest($request));

        return redirect()->route('cliente.index')->with('message', 'Registro criado');;
    }
}