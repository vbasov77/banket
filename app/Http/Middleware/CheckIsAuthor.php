<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckIsAuthor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (Auth::user()->isAuthor(Auth::user()->id)) {
                return $next($request);
            }
            return redirect('/front');
        } else {
            return \redirect()->route('login');
        }

    }
}
