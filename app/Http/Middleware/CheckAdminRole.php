<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role_id == 1) {
            return $next($request);
        }

        abort(403, 'Acesso não autorizado.');
    }
}
