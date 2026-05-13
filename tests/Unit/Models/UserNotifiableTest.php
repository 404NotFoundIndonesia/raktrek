<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class UserNotifiableTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_notifications_relationship(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\MorphMany::class,
            $user->notifications()
        );
    }

    public function test_notifications_return_database_notification_instances(): void
    {
        $user = User::factory()->create();

        $user->notifications()->create([
            'id'   => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => json_encode(['message' => 'hello']),
        ]);

        $this->assertInstanceOf(DatabaseNotification::class, $user->notifications()->first());
    }

    public function test_unread_notifications_scope_filters_correctly(): void
    {
        $user = User::factory()->create();

        $readId   = \Illuminate\Support\Str::uuid();
        $unreadId = \Illuminate\Support\Str::uuid();

        $user->notifications()->create([
            'id'      => $readId,
            'type'    => 'TestNotification',
            'data'    => json_encode(['message' => 'read']),
            'read_at' => now(),
        ]);

        $user->notifications()->create([
            'id'   => $unreadId,
            'type' => 'TestNotification',
            'data' => json_encode(['message' => 'unread']),
        ]);

        $unread = $user->unreadNotifications;

        $this->assertCount(1, $unread);
        $this->assertEquals($unreadId, $unread->first()->id);
    }

    public function test_wants_email_notification_defaults_to_true(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->wantsEmailNotification('book_available_email'));
    }

    public function test_wants_email_notification_respects_preference(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['book_available_email' => false],
        ]);

        $this->assertFalse($user->wantsEmailNotification('book_available_email'));
    }
}
