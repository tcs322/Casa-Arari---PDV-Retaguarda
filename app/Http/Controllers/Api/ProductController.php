<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function listarCafeteria(): JsonResponse
    {
        $produtos = Product::where('tipo', 'CAFETERIA')
                   ->orWhere('tipo', 'LIVRARIA')
                   ->get();

        return response()->json([
            'success' => true,
            'data' => $produtos,
        ])->header('Access-Control-Allow-Origin', '*');
    }
}
