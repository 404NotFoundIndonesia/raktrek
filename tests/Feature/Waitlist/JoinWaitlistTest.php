<?php

namespace Tests\Feature\Waitlist;

use App\Models\Book;
use App\Models\User;
use App\Models\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JoinWaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_join_waitlist_for_unavailable_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 0]);

        $response = $this->actingAs($user)->post('/waitlists', ['book_id' => $book->id]);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('waiting_lists', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_finish_date_is_7_days_from_today(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 0]);

        $this->actingAs($user)->post('/waitlists', ['book_id' => $book->id]);

        $entry = WaitingList::where('user_id', $user->id)->first();

        $this->assertTrue(
            now()->addDays(7)->startOfDay()->eq($entry->finish_date->startOfDay())
        );
    }

    public function test_joining_available_book_returns_error(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 1]);

        $response = $this->actingAs($user)->post('/waitlists', ['book_id' => $book->id]);

        $response->assertSessionHasErrors('book_id');
        $this->assertDatabaseEmpty('waiting_lists');
    }

    public function test_duplicate_join_returns_error(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 0]);
        WaitingList::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->post('/waitlists', ['book_id' => $book->id]);

        $response->assertSessionHasErrors('book_id');
        $this->assertDatabaseCount('waiting_lists', 1);
    }

    public function test_guest_redirected_to_login(): void
    {
        $book = Book::factory()->create(['availability' => 0]);

        $response = $this->post('/waitlists', ['book_id' => $book->id]);

        $response->assertRedirect(route('auth.login'));
    }

    public function test_nonexistent_book_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/waitlists', ['book_id' => 9999]);

        $response->assertNotFound();
    }
}
