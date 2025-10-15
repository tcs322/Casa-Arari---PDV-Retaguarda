<?php

namespace App\Http\Controllers\App\Venda;

use App\Actions\Venda\VendaAction;
use App\DTO\Venda\VendaShowDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Venda\VendaShowRequest;
use Illuminate\Http\Request;

class VendaController extends Controller
{
    public function __construct(
        protected VendaAction $action
    ) {}

    public function create()
    {
        return view ('app.venda.create');
    }

    public function pagamento()
    {
        return view('app.venda.pagamento');
    }

    public function index(Request $request)
    {
        $vendas = $this->action->paginate(
            page: $request->get('page', 1),
            totalPerPage: $request->get('per_page', 6),
            filter: $request->get('filter'),
        );

        $filters = ['filter' => $request->get('filter', '')];
        
        return view('app.venda.index', compact('vendas', 'filters'));
    }

    public function show(string $uuid, VendaShowRequest $request)
    {
        $request->merge([
            "uuid" => $uuid
        ]);

        $venda = $this->action->show(VendaShowDTO::makeFromRequest($request));

        return view('app.venda.show', ["venda" => $venda]);
    }
}