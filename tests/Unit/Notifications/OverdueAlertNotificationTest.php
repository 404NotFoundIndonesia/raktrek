<?php

namespace Tests\Unit\Notifications;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use App\Notifications\OverdueAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverdueAlertNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_returns_database_and_mail_when_enabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['overdue_alert_email' => true],
        ]);
        $book      = Book::factory()->create();
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $notification = new OverdueAlertNotification($borrowing);

        $this->assertEquals(['database', 'mail'], $notification->via($user));
    }

    public function test_via_returns_only_database_when_disabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['overdue_alert_email' => false],
        ]);
        $book      = Book::factory()->create();
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $notification = new OverdueAlertNotification($borrowing);

        $this->assertEquals(['database'], $notification->via($user));
    }

    public function test_to_mail_contains_book_title(): void
    {
        $user      = User::factory()->create();
        $book      = Book::factory()->create(['title' => '1984']);
        $borrowing = Borrowing::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'due_date'    => now()->subDays(2),
        ]);

        $notification = new OverdueAlertNotification($borrowing);
        $mail         = $notification->toMail($user);

        $this->assertStringContainsString('1984', $mail->subject);
    }

    public function test_to_array_contains_borrowing_id(): void
    {
        $user      = User::factory()->create();
        $book      = Book::factory()->create();
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $notification = new OverdueAlertNotification($borrowing);
        $data         = $notification->toArray($user);

        $this->assertEquals($borrowing->id, $data['borrowing_id']);
        $this->assertArrayHasKey('due_date', $data);
    }
}
