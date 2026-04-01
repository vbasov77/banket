<?php

namespace App\Http\Middleware;

use App\Services\CityService;
use Closure;
use Illuminate\Http\Request;

class LoadUserCity
{
    public function handle(Request $request, Closure $next)
    {
        app(CityService::class)->getUserCity();
        return $next($request);
    }
}
