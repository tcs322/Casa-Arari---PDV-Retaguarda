<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\MustChangePasswordEnum;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password === MustChangePasswordEnum::YES()->value) {
            if (! $request->routeIs('password.change') && ! $request->routeIs('password.update')) {
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
