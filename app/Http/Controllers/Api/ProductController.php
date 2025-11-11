<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function listarCafeteria(): JsonResponse
    {
        $produtos = Product::where(function ($query) {
                $query->where('tipo', 'CAFETERIA')
                    ->orWhere('tipo', 'LIVRARIA');
            })
            ->where('estoque', '>', 0)
            ->get();


        return response()->json([
            'success' => true,
            'data' => $produtos,
        ])->header('Access-Control-Allow-Origin', '*');
    }
}
