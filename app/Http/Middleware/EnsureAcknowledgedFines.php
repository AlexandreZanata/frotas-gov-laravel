<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Fine;

class EnsureAcknowledgedFines
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);
        // permitir exceções
        if ($request->routeIs('fines.*') || $request->routeIs('logout') || $request->routeIs('profile.*')) {
            return $next($request);
        }
        // motoristas (assumindo role_id 4) precisam reconhecer multas aguardando pagamento
        if ($user->role_id == 4) {
            $pending = Fine::pendingAcknowledgement($user->id)->count();
            if ($pending > 0) {
                return redirect()->route('fines.pending')->with('warning','Você possui multas que exigem ciência antes de continuar.');
            }
        }
        return $next($request);
    }
}

