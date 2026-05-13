<?php

namespace Tests\Unit\Notifications;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use App\Notifications\DueDateReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DueDateReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_returns_database_and_mail_when_enabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['due_date_reminder_email' => true],
        ]);
        $book      = Book::factory()->create();
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $notification = new DueDateReminderNotification($borrowing);

        $this->assertEquals(['database', 'mail'], $notification->via($user));
    }

    public function test_via_returns_only_database_when_disabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['due_date_reminder_email' => false],
        ]);
        $book      = Book::factory()->create();
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $notification = new DueDateReminderNotification($borrowing);

        $this->assertEquals(['database'], $notification->via($user));
    }

    public function test_to_mail_contains_due_date(): void
    {
        $user      = User::factory()->create();
        $book      = Book::factory()->create(['title' => 'Moby Dick']);
        $borrowing = Borrowing::factory()->create([
            'user_id'  => $user->id,
            'book_id'  => $book->id,
            'due_date' => now()->addDays(3),
        ]);

        $notification = new DueDateReminderNotification($borrowing);
        $mail         = $notification->toMail($user);

        $this->assertStringContainsString('Moby Dick', $mail->subject);
    }

    public function test_to_array_contains_borrowing_id_and_due_date(): void
    {
        $user      = User::factory()->create();
        $book      = Book::factory()->create();
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $notification = new DueDateReminderNotification($borrowing);
        $data         = $notification->toArray($user);

        $this->assertEquals($borrowing->id, $data['borrowing_id']);
        $this->assertArrayHasKey('due_date', $data);
    }
}
