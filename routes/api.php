<?php

use App\Http\Controllers\Api\ProductController;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PedidoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// routes/api.php

Route::post('/imprimir-direto', function (Request $request) {
    $texto = $request->input('texto');
    $vendaId = $request->input('venda_id');
    $impressora = $request->input('impressora', '71840');

    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

    Log::info("🖨️ Iniciando impressão da venda #{$vendaId}");

    try {
        $printerServerUrl = "http://host.docker.internal:8051";
        $payload = [
            'texto' => $texto,
            'impressora' => $impressora,
        ];
    
        $response = Http::post($printerServerUrl, $payload);
    
        return response()->json($response->json());
    } catch (\Exception $e) {
        Log::error("Erro ao enviar ao servidor de impressão", ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }    
})->withoutMiddleware(['web']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/produtos/cafeteria', [ProductController::class, 'listarCafeteria']);

Route::get('/pedidos', [PedidoController::class, 'index']);
Route::post('/pedidos', [PedidoController::class, 'store']);
Route::post('/pedidos/{pedido}/preparado', [PedidoController::class, 'marcarComoPreparado']);


