<?php

namespace Tests\Unit\Notifications;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use App\Notifications\NewBookByFavouriteAuthorNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewBookNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_returns_database_and_mail_when_enabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['new_book_email' => true],
        ]);
        $book         = Book::factory()->create();
        $notification = new NewBookByFavouriteAuthorNotification($book);

        $this->assertEquals(['database', 'mail'], $notification->via($user));
    }

    public function test_via_returns_only_database_when_disabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => ['new_book_email' => false],
        ]);
        $book         = Book::factory()->create();
        $notification = new NewBookByFavouriteAuthorNotification($book);

        $this->assertEquals(['database'], $notification->via($user));
    }

    public function test_to_mail_contains_book_title(): void
    {
        $user         = User::factory()->create();
        $author       = Author::factory()->create(['name' => 'George Orwell']);
        $book         = Book::factory()->create(['title' => 'Animal Farm', 'author_id' => $author->id]);
        $book->load('author');
        $notification = new NewBookByFavouriteAuthorNotification($book);

        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Animal Farm', $mail->subject);
        $this->assertStringContainsString('George Orwell', $mail->subject);
    }

    public function test_to_array_contains_book_id_and_title(): void
    {
        $user         = User::factory()->create();
        $book         = Book::factory()->create(['title' => 'Brave New World']);
        $notification = new NewBookByFavouriteAuthorNotification($book);

        $data = $notification->toArray($user);

        $this->assertEquals($book->id, $data['book_id']);
        $this->assertStringContainsString('Brave New World', $data['message']);
    }

    public function test_implements_should_queue(): void
    {
        $book = Book::factory()->make();

        $this->assertInstanceOf(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            new NewBookByFavouriteAuthorNotification($book)
        );
    }
}
