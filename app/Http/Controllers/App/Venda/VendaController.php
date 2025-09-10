<?php

namespace App\Http\Controllers\App\Venda;

use App\Http\Controllers\Controller;

class VendaController extends Controller
{
    public function __construct() {}

    public function create()
    {
        return view ('app.venda.create');
    }
}