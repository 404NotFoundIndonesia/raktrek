<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_update_notification_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile/notification-preferences', [
            'book_available_email'    => false,
            'due_date_reminder_email' => true,
            'overdue_alert_email'     => false,
            'new_book_email'          => true,
        ]);

        $response->assertRedirect();

        $prefs = $user->fresh()->notification_preferences;
        $this->assertFalse($prefs['book_available_email']);
        $this->assertTrue($prefs['due_date_reminder_email']);
        $this->assertFalse($prefs['overdue_alert_email']);
        $this->assertTrue($prefs['new_book_email']);
    }

    public function test_profile_page_includes_user_with_notification_preferences(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['book_available_email' => false],
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertInertia(fn ($page) =>
            $page->component('Profile/Index')
                 ->has('user.notification_preferences')
        );
    }

    public function test_email_skipped_when_preference_disabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['book_available_email' => false],
        ]);

        $this->assertFalse($user->wantsEmailNotification('book_available_email'));
    }

    public function test_guest_cannot_update_preferences(): void
    {
        $response = $this->put('/profile/notification-preferences', [
            'book_available_email' => false,
        ]);

        $response->assertRedirect(route('auth.login'));
    }

    public function test_invalid_preference_value_returns_422(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile/notification-preferences', [
            'book_available_email' => 'not-a-boolean',
        ]);

        $response->assertSessionHasErrors('book_available_email');
    }
}
