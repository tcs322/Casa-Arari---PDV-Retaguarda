<?php

namespace App\Http\Controllers\App\Cliente;

use App\Actions\Cliente\ClienteAction;
use App\DTO\Cliente\ClienteEditDTO;
use App\DTO\Cliente\ClienteStoreDTO;
use App\DTO\Cliente\ClienteUpdateDTO;
use App\Http\Requests\App\Cliente\ClienteStoreRequest;
use App\Http\Requests\App\Cliente\ClienteUpdateRequest;
use Illuminate\Http\Request;

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

        return redirect()->route('cliente.index')->with('message', 'Registro criado');
    }

    public function index(Request $request)
    {
        $clientes = $this->action->index(
            page: $request->get('page', 1),
            totalPerPage:  $request->get('totalPerPage', 15),
            filter: $request->get('filter', null),
        );

        $filters = ['filter' => $request->get('filter', null)];

        return view('app.cliente.index', compact('clientes', 'filters'));
    }

    public function edit(string $uuid, Request $request)
    {
        $request->merge([
            "uuid" => $uuid
        ]);

        $formData = [];

        $cliente = $this->action->edit(ClienteEditDTO::makeFromRequest($request));

        return view('app.cliente.edit', [
            "cliente" => $cliente,
            "formData" => $formData
        ]);
    }

    public function update(ClienteUpdateRequest $request)
    {
        $this->action->update(ClienteUpdateDTO::makeFromRequest($request));

        return redirect()->route('cliente.index')->with('message', 'Registro Atualizado');
    }
}