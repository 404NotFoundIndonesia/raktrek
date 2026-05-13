<?php

namespace Tests\Feature\Borrowing;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\WaitingList;
use App\Notifications\BookAvailableNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_return_own_book(): void
    {
        $user     = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->patch("/borrowings/{$borrowing->id}/return");

        $response->assertRedirect();

        $this->assertNotNull($borrowing->fresh()->return_date);
        $this->assertEquals(1, $book->fresh()->availability);
    }

    public function test_return_date_set_to_today(): void
    {
        $user     = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $this->actingAs($user)->patch("/borrowings/{$borrowing->id}/return");

        $this->assertTrue(
            now()->startOfDay()->eq($borrowing->fresh()->return_date->startOfDay())
        );
    }

    public function test_non_owner_cannot_return_borrowing(): void
    {
        $owner    = User::factory()->create();
        $other    = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

        $response = $this->actingAs($other)->patch("/borrowings/{$borrowing->id}/return");

        $response->assertForbidden();
        $this->assertNull($borrowing->fresh()->return_date);
    }

    public function test_already_returned_borrowing_returns_422(): void
    {
        $user     = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 1]);
        $borrowing = Borrowing::factory()->returned()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->patch("/borrowings/{$borrowing->id}/return");

        $response->assertSessionHasErrors('borrowing');
    }

    public function test_notification_sent_to_first_in_waitlist_on_return(): void
    {
        Notification::fake();

        $owner      = User::factory()->create();
        $waiter1    = User::factory()->create();
        $waiter2    = User::factory()->create();
        $book       = Book::factory()->create(['availability' => 0]);
        $borrowing  = Borrowing::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

        WaitingList::factory()->create(['user_id' => $waiter1->id, 'book_id' => $book->id]);
        WaitingList::factory()->create(['user_id' => $waiter2->id, 'book_id' => $book->id]);

        $this->actingAs($owner)->patch("/borrowings/{$borrowing->id}/return");

        Notification::assertSentTo($waiter1, BookAvailableNotification::class);
        Notification::assertNotSentTo($waiter2, BookAvailableNotification::class);
    }

    public function test_no_notification_when_no_waitlist(): void
    {
        Notification::fake();

        $user     = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $this->actingAs($user)->patch("/borrowings/{$borrowing->id}/return");

        Notification::assertNothingSent();
    }

    public function test_book_availability_set_to_true_on_return(): void
    {
        $user     = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $this->actingAs($user)->patch("/borrowings/{$borrowing->id}/return");

        $this->assertEquals(1, $book->fresh()->availability);
    }
}
