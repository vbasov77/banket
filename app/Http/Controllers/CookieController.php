<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CookieController extends Controller
{
    public function accept(Request $request): JsonResponse
    {
        session(['cookie_accepted' => true]);

        return response()->json(['status' => 'ok']);
    }
}
