<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($role === 'almoxarife' && !$user->isAlmoxarife()) {
            abort(403, 'Acesso restrito ao Almoxarife.');
        }

        if ($role === 'coordenador' && !$user->isCoordenador()) {
            abort(403, 'Acesso restrito ao Coordenador.');
        }

        return $next($request);
    }
}
