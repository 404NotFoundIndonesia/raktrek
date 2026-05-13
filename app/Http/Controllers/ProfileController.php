<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Profile/Index', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('success', 'Profile updated.');
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        return back()->with('success', 'Password changed.');
    }

    public function updateNotificationPreferences(\Illuminate\Http\Request $request): RedirectResponse
    {
        $request->validate([
            'book_available_email'     => ['boolean'],
            'due_date_reminder_email'  => ['boolean'],
            'overdue_alert_email'      => ['boolean'],
            'new_book_email'           => ['boolean'],
        ]);

        $prefs = $request->only([
            'book_available_email',
            'due_date_reminder_email',
            'overdue_alert_email',
            'new_book_email',
        ]);

        $request->user()->update(['notification_preferences' => $prefs]);

        return back()->with('success', 'Notification preferences updated.');
    }
}
