<?php

namespace App\Http\Controllers;

class CookiePolicyController extends Controller
{
    public function show()
    {
        return view('privacy.cookies');
    }
}
