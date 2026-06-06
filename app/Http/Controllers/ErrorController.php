<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function unauthorized(Request $request)
    {
        $message = $request->session()->get('error', 'У вас нет прав для выполнения этого действия');

        return view('errors.unauthorized', [
            'message' => $message
        ]);
    }
}
