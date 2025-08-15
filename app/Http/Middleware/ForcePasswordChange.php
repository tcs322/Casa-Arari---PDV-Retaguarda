<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->must_change_password) {
            if (!$request->routeIs('auth.password.change.form') &&
                !$request->routeIs('auth.password.change')) {
                return redirect()->route('auth.password.change.form');
            }
        }

        return $next($request);
    }
}
