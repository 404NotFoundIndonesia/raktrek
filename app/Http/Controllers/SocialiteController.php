<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::withTrashed()->where('email', $googleUser->getEmail())->first();

        if ($user && $user->trashed()) {
            return redirect()->route('auth.login')
                ->withErrors('Your account has been deactivated.');
        }

        if ($user) {
            // Link google_id if not already set
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $user = User::create([
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password'  => \Illuminate\Support\Str::password(32),
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }
}
