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
    public function show(): View|RedirectResponse
    {
        // Если роль уже выбрана — сразу уводим дальше
        if (Auth::user()->role_id) {
            return $this->redirect();
        }

        $roles = Role::whereIn('name', ['soon_banquet', 'restaurateur'])->get();

        return view('auth.select-role', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:soon_banquet,restaurateur'],
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
        $user = User::with('role')->find(1);

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

        return redirect()->route('profile.show');
    }
}
