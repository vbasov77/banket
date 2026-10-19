<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoleSelectionController extends Controller
{
    /**
     * @return View|RedirectResponse
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (Auth::user()->role_id) {
            return $this->redirect();
        }

        $message = $request->message ?? null;

        // Роли берём строго в нужном порядке: исполнитель — последним
        $roles = collect(['soon_banquet', 'restaurateur', 'performer'])
            ->map(fn ($name) => Role::where('name', $name)->first())
            ->filter();

        return view('auth.select-role', ['roles' => $roles, 'message' => $message]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:soon_banquet,restaurateur,performer'],
        ]);

        $role = Role::where('name', $request->role)->firstOrFail();
        Auth::user()->forceFill(['role_id' => $role->id])->save();

        return $this->redirect();
    }


    /**
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        $user = User::with('role')->find(Auth::id());

        if (!$user->role_id) {
            return redirect()->route('role.select');
        }

        // Разные пути для разных ролей
        if ($user->isAdmin()) {
            return redirect()->route('my.obj');
        }

        if ($user->isRestaurateur()) {
            return redirect()->route('my.obj');
        }

        if ($user->isSoonBanquet()) {
            return redirect()->route('profile.show');
        }

        if ($user->isPerformer()) {
            return redirect()->route('catalog.request.my_category');
        }

        return redirect()->route('profile.show');
    }
}
