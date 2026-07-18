<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * @return View
     */
    public function showAdminPanel(): View
    {
        return view('admin.admin_panel');

    }
}