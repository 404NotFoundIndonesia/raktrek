<?php

namespace Tests\Unit\Notifications;

use App\Models\Book;
use App\Models\User;
use App\Notifications\BookAvailableNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAvailableNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_returns_database_and_mail_when_email_enabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['book_available_email' => true],
        ]);
        $book         = Book::factory()->create();
        $notification = new BookAvailableNotification($book);

        $this->assertEquals(['database', 'mail'], $notification->via($user));
    }

    public function test_via_returns_only_database_when_email_disabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['book_available_email' => false],
        ]);
        $book         = Book::factory()->create();
        $notification = new BookAvailableNotification($book);

        $this->assertEquals(['database'], $notification->via($user));
    }

    public function test_to_mail_contains_book_title(): void
    {
        $user         = User::factory()->create();
        $book         = Book::factory()->create(['title' => 'The Great Gatsby']);
        $notification = new BookAvailableNotification($book);

        $mail = $notification->toMail($user);

        $this->assertStringContainsString('The Great Gatsby', $mail->subject);
    }

    public function test_to_array_contains_book_id_and_title(): void
    {
        $user         = User::factory()->create();
        $book         = Book::factory()->create(['title' => 'Dune']);
        $notification = new BookAvailableNotification($book);

        $data = $notification->toArray($user);

        $this->assertEquals($book->id, $data['book_id']);
        $this->assertStringContainsString('Dune', $data['message']);
    }
}
