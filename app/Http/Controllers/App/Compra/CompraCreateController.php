<?php

namespace App\Http\Controllers\App\Compra;

use App\Actions\Compra\CompraCreateAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class CompraCreateController extends Controller
{
    public function create(Request $request, CompraCreateAction $action)
    {
        $formData = $action->execute($request->get('loteUuid'));

        return view('app.compra.create', [
            'formData' => $formData
        ]);
    }
}
