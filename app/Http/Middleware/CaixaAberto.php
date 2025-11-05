<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Caixa;
use Illuminate\Support\Facades\Auth;

class CaixaAberto
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ Verifica se o usuário está autenticado
        $user = Auth::user();

        // ✅ Verifica se existe um caixa aberto para o usuário atual
        $caixaAberto = Caixa::where('usuario_uuid', $user->uuid)
            ->whereNull('data_fechamento')
            ->first();

        if (!$caixaAberto) {
            // Nenhum caixa aberto → bloqueia o acesso à frente de caixa
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum caixa aberto. Abra um caixa antes de acessar o frente de caixa.'
                ], 403);
            }

            return redirect()
                ->route('dashboard.index')
                ->with('warning', [
                    'title' => 'Caixa Fechado',
                    'message' => 'Você precisa abrir o caixa antes de acessar a frente de caixa.'
                ]);
        }

        // ✅ Tudo certo → segue normalmente
        return $next($request);
    }
}
