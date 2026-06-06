<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subj;

class AuthorOrAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $subjId = $request->route('id');
        $subj = Subj::findOrFail($subjId);

        // Разрешить доступ, если админ ИЛИ автор
        if (!$user->isAdmin() && !$subj->isAuthor()) {
            abort(403, 'Доступ запрещён: требуется статус администратора или автора материала');
        }

        return $next($request);
    }
}
