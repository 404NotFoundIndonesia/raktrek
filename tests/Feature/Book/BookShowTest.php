<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\BookImage;
use App\Models\Borrowing;
use App\Models\Review;
use App\Models\User;
use App\Models\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_show_returns_required_props(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Books/Show')
                 ->has('book')
                 ->has('book.author')
                 ->has('book.genres')
                 ->has('book.images')
                 ->has('book.reviews')
                 ->has('averageRating')
                 ->has('availabilityLabel')
                 ->has('userBorrowing')
                 ->has('userWaitlistPosition')
        );
    }

    public function test_nonexistent_book_returns_404(): void
    {
        $response = $this->get('/books/9999');

        $response->assertNotFound();
    }

    public function test_soft_deleted_book_returns_404(): void
    {
        $book = Book::factory()->create();
        $book->delete();

        $response = $this->get("/books/{$book->id}");

        $response->assertNotFound();
    }

    public function test_average_rating_calculated_correctly(): void
    {
        $book = Book::factory()->create();
        Review::factory()->create(['book_id' => $book->id, 'rate' => 4]);
        Review::factory()->create(['book_id' => $book->id, 'rate' => 2]);

        $response = $this->get("/books/{$book->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('averageRating', 3)
        );
    }

    public function test_user_borrowing_prop_set_when_member_has_active_loan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->get("/books/{$book->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('userBorrowing.id', $borrowing->id)
        );
    }

    public function test_user_waitlist_position_set_when_member_is_in_queue(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book  = Book::factory()->create(['availability' => 0]);

        WaitingList::factory()->create(['user_id' => $user1->id, 'book_id' => $book->id]);
        WaitingList::factory()->create(['user_id' => $user2->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user2)->get("/books/{$book->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('userWaitlistPosition', 2)
        );
    }

    public function test_reviews_include_user_relation(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        Review::factory()->create(['book_id' => $book->id, 'user_id' => $user->id, 'comment' => 'Great!']);

        $response = $this->get("/books/{$book->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('book.reviews.0.user')
        );
    }

    public function test_book_images_included(): void
    {
        $book = Book::factory()->create();
        BookImage::factory()->create(['book_id' => $book->id]);

        $response = $this->get("/books/{$book->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('book.images', 1)
        );
    }
}
