<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle($request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'ADMIN') {
            return $next($request);
        }

        abort(403, 'Acesso negado.');
    }
}
