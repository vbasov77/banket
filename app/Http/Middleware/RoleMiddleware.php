<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRoles = auth()->user()->roles->pluck('name')->toArray(); // или другой способ получения ролей
        $allowedRoles = explode('|', implode('|', $roles));

        if (empty(array_intersect($userRoles, $allowedRoles))) {
            abort(403, 'У вас нет доступа к этому разделу');
        }

        return $next($request);
    }
}
