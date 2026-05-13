<?php

namespace Tests\Feature\Borrowing;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_borrow_available_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 1]);

        $response = $this->actingAs($user)->post('/borrowings', ['book_id' => $book->id]);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('borrowings', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertEquals(0, $book->fresh()->availability);
    }

    public function test_due_date_is_14_days_from_today(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 1]);

        $this->actingAs($user)->post('/borrowings', ['book_id' => $book->id]);

        $borrowing = Borrowing::where('user_id', $user->id)->first();

        $this->assertTrue(
            now()->addDays(14)->startOfDay()->eq($borrowing->due_date->startOfDay())
        );
    }

    public function test_borrowing_unavailable_book_returns_422(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 0]);

        $response = $this->actingAs($user)->post('/borrowings', ['book_id' => $book->id]);

        $response->assertSessionHasErrors('book_id');
        $this->assertDatabaseEmpty('borrowings');
    }

    public function test_borrowing_already_checked_out_book_returns_422(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 0]);
        Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->post('/borrowings', ['book_id' => $book->id]);

        $response->assertSessionHasErrors('book_id');
        $this->assertDatabaseCount('borrowings', 1);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $book = Book::factory()->create(['availability' => 1]);

        $response = $this->post('/borrowings', ['book_id' => $book->id]);

        $response->assertRedirect(route('auth.login'));
    }

    public function test_book_id_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/borrowings', []);

        $response->assertSessionHasErrors('book_id');
    }

    public function test_nonexistent_book_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/borrowings', ['book_id' => 9999]);

        $response->assertNotFound();
    }
}
