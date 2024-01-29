<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function login(): Response|RedirectResponse {
        if (!auth()->guest()) {
            return redirect('/');
        }

        return Inertia::render('Auth/Login');
    }

    public function signIn(LoginRequest $request): RedirectResponse {
        $credentials = $request->getCredential();

        if (!Auth::validate($credentials)) return back()->withErrors(trans('auth.failed'));

        $user = Auth::getProvider()->retrieveByCredentials($credentials);
        Auth::login($user, !is_null($request->get('remember-me')));

        return redirect('/');
    }

    public function register(): Response {
        if (!auth()->guest()) {
            return redirect('/');
        }

        return Inertia::render('Auth/Register');
    }

    public function signUp(RegisterRequest $request): RedirectResponse {
        $user = User::create($request->validated());
        Auth::login($user);

        return redirect('/');
    }

    public function signOut(): RedirectResponse {
        Auth::logout();

        return redirect('/');
    }

    public function googleOneTap(Request $request): RedirectResponse {
        return redirect('/');
    }
}
