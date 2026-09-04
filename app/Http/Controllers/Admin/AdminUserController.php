<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminUserRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    private AdminUserRegistrationService $service;

    public function __construct(AdminUserRegistrationService $service)
    {
        $this->service = $service;
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        // Создаём пользователя и отправляем письмо
        $result = $this->service->registerByAdmin(
            $request->input('name'),
            $request->input('email'),
            Auth::user()
        );

        if ($result['success']) {
            return redirect()
                ->route('admin.users.create')
                ->with('success', 'Пользователь создан, пароль отправлен на почту.');
        }

        return back()->withErrors(['error' => $result['message'] ?? 'Ошибка при регистрации']);
    }
}