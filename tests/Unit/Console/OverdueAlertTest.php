<?php

namespace Tests\Unit\Console;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use App\Notifications\OverdueAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OverdueAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_alert_for_overdue_borrowing(): void
    {
        Notification::fake();

        $user     = User::factory()->create();
        $book     = Book::factory()->create();
        Borrowing::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'due_date'    => now()->subDays(2),
            'return_date' => null,
        ]);

        $this->artisan('notifications:send-overdue-alerts');

        Notification::assertSentTo($user, OverdueAlertNotification::class);
    }

    public function test_does_not_send_alert_for_returned_borrowing(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();
        Borrowing::factory()->returned()->create([
            'user_id'  => $user->id,
            'book_id'  => $book->id,
            'due_date' => now()->subDays(2),
        ]);

        $this->artisan('notifications:send-overdue-alerts');

        Notification::assertNothingSent();
    }

    public function test_does_not_resend_if_already_notified_today(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();
        Borrowing::factory()->create([
            'user_id'                  => $user->id,
            'book_id'                  => $book->id,
            'due_date'                 => now()->subDays(2),
            'return_date'              => null,
            'last_overdue_notified_at' => now(),
        ]);

        $this->artisan('notifications:send-overdue-alerts');

        Notification::assertNothingSent();
    }

    public function test_resends_if_last_notified_was_yesterday(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();
        Borrowing::factory()->create([
            'user_id'                  => $user->id,
            'book_id'                  => $book->id,
            'due_date'                 => now()->subDays(3),
            'return_date'              => null,
            'last_overdue_notified_at' => now()->subDay(),
        ]);

        $this->artisan('notifications:send-overdue-alerts');

        Notification::assertSentTo($user, OverdueAlertNotification::class);
    }

    public function test_updates_last_overdue_notified_at_after_sending(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();
        $borrowing = Borrowing::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'due_date'    => now()->subDays(2),
            'return_date' => null,
        ]);

        $this->artisan('notifications:send-overdue-alerts');

        $this->assertNotNull($borrowing->fresh()->last_overdue_notified_at);
        $this->assertTrue($borrowing->fresh()->last_overdue_notified_at->isToday());
    }
}
