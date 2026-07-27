<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * @param Request $request
     * @return View
     */
    public function showAdminPanel(Request $request): View
    {
        !empty($request->message) ? $message = $request->message : $message = null;

        return view('admin.admin_panel', ['message' => $message]);

    }
}