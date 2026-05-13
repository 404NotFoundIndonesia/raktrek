<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationCentreTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_view_their_notifications(): void
    {
        $user = User::factory()->create();

        $user->notifications()->create([
            'id'   => Str::uuid(),
            'type' => 'TestNotification',
            'data' => json_encode(['message' => 'Hello']),
        ]);

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Notifications/Index')
                 ->has('notifications', 1)
        );
    }

    public function test_member_cannot_see_other_users_notifications(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user2->notifications()->create([
            'id'   => Str::uuid(),
            'type' => 'TestNotification',
            'data' => json_encode(['message' => 'Hello user2']),
        ]);

        $response = $this->actingAs($user1)->get('/notifications');

        $response->assertInertia(fn ($page) =>
            $page->has('notifications', 0)
        );
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/notifications');

        $response->assertRedirect(route('auth.login'));
    }

    public function test_mark_as_read_sets_read_at(): void
    {
        $user = User::factory()->create();

        $notifId = Str::uuid();
        $user->notifications()->create([
            'id'   => $notifId,
            'type' => 'TestNotification',
            'data' => json_encode(['message' => 'Hello']),
        ]);

        $response = $this->actingAs($user)->post("/notifications/{$notifId}/read");

        $response->assertRedirect();
        $this->assertNotNull($user->notifications()->find($notifId)->read_at);
    }

    public function test_non_owner_cannot_mark_as_read(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $notifId = Str::uuid();
        $user1->notifications()->create([
            'id'   => $notifId,
            'type' => 'TestNotification',
            'data' => json_encode(['message' => 'Hello']),
        ]);

        $response = $this->actingAs($user2)->post("/notifications/{$notifId}/read");

        $response->assertForbidden();
        $this->assertNull($user1->notifications()->find($notifId)->read_at);
    }

    public function test_guest_redirected_to_login_on_mark_as_read(): void
    {
        $user    = User::factory()->create();
        $notifId = Str::uuid();
        $user->notifications()->create([
            'id'   => $notifId,
            'type' => 'TestNotification',
            'data' => json_encode(['message' => 'Hello']),
        ]);

        $response = $this->post("/notifications/{$notifId}/read");

        $response->assertRedirect(route('auth.login'));
    }
}
