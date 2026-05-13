<?php

namespace Tests\Unit\Console;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use App\Notifications\DueDateReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DueDateReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_reminder_for_borrowing_due_in_3_days(): void
    {
        Notification::fake();

        $user     = User::factory()->create();
        $book     = Book::factory()->create();
        $borrowing = Borrowing::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'due_date'    => now()->addDays(3),
            'return_date' => null,
        ]);

        $this->artisan('notifications:send-due-date-reminders');

        Notification::assertSentTo($user, DueDateReminderNotification::class);
    }

    public function test_does_not_send_reminder_for_borrowing_not_due_in_3_days(): void
    {
        Notification::fake();

        $user     = User::factory()->create();
        $book     = Book::factory()->create();
        Borrowing::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'due_date'    => now()->addDays(5),
            'return_date' => null,
        ]);

        $this->artisan('notifications:send-due-date-reminders');

        Notification::assertNothingSent();
    }

    public function test_does_not_send_reminder_for_already_returned_borrowing(): void
    {
        Notification::fake();

        $user     = User::factory()->create();
        $book     = Book::factory()->create();
        Borrowing::factory()->returned()->create([
            'user_id'  => $user->id,
            'book_id'  => $book->id,
            'due_date' => now()->addDays(3),
        ]);

        $this->artisan('notifications:send-due-date-reminders');

        Notification::assertNothingSent();
    }

    public function test_sends_reminders_to_multiple_users(): void
    {
        Notification::fake();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Borrowing::factory()->create(['user_id' => $user1->id, 'book_id' => $book1->id, 'due_date' => now()->addDays(3)]);
        Borrowing::factory()->create(['user_id' => $user2->id, 'book_id' => $book2->id, 'due_date' => now()->addDays(3)]);

        $this->artisan('notifications:send-due-date-reminders');

        Notification::assertSentTo($user1, DueDateReminderNotification::class);
        Notification::assertSentTo($user2, DueDateReminderNotification::class);
    }
}
