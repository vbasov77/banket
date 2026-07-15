<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest('login');
        }

        // Если у пользователя нет роли вообще — доступ запрещён
        if (! $user->role) {
            abort(403, 'Доступ запрещён: роль не назначена');
        }

        // Проверяем, есть ли роль пользователя в разрешённом списке
        if (! in_array($user->role->name, $roles, true)) {
            abort(403, 'Недостаточно прав для доступа к этой странице');
        }

        return $next($request);
    }
}
