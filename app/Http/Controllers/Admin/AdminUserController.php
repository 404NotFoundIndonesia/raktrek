<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::withTrashed();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/Users/Index', [
            'users'   => $query->orderBy('name')->paginate(20)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        $user->delete();

        return back()->with('success', 'User deactivated.');
    }

    public function activate(int $user): RedirectResponse
    {
        $found = User::withTrashed()->findOrFail($user);
        $found->restore();

        return back()->with('success', 'User activated.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => ['required', Rule::in(['member', 'staff'])],
        ]);

        $user->update(['role' => $request->role]);

        return back()->with('success', 'User role updated.');
    }
}
