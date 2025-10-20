<?php

namespace App\Http\Controllers\App\Nota;

use App\Actions\Nota\NotaAction;
use App\Http\Controllers\Controller;
use App\Models\Nota;
use Illuminate\Http\Request;

class NotaController extends Controller
{
    public function __construct(
        protected NotaAction $action
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $notas = $this->action->paginate(
            page: $request->get('page', 1),
            totalPerPage: $request->get('per_page', 6),
            filter: $request->get('filter'),
        );

        $filters = ['filter' => $request->get('filter', '')];
        
        return view('app.nota.index', compact('notas', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('app.nota.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Nota $nota)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nota $nota)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nota $nota)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nota $nota)
    {
        //
    }

    public function createWithoutXml()
    {
        $formData = ['tipo_nota' => []];

        return view('app.nota.create-without-xml', compact('formData'));
    }
}
