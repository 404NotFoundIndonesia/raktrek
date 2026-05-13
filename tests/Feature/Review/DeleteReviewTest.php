<?php

namespace Tests\Feature\Review;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_own_review(): void
    {
        $user   = User::factory()->create();
        $book   = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_staff_can_delete_any_review(): void
    {
        $staff  = User::factory()->staff()->create();
        $owner  = User::factory()->create();
        $book   = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

        $response = $this->actingAs($staff)->delete("/reviews/{$review->id}");

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_non_owner_member_cannot_delete(): void
    {
        $owner  = User::factory()->create();
        $other  = User::factory()->create();
        $book   = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

        $response = $this->actingAs($other)->delete("/reviews/{$review->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_average_rating_recalculated_after_deletion(): void
    {
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $book   = Book::factory()->create();

        Review::factory()->create(['user_id' => $user1->id, 'book_id' => $book->id, 'rate' => 5]);
        $review = Review::factory()->create(['user_id' => $user2->id, 'book_id' => $book->id, 'rate' => 1]);

        $this->actingAs($user2)->delete("/reviews/{$review->id}");

        $response = $this->actingAs($user1)->get("/books/{$book->id}");

        $response->assertInertia(fn ($page) =>
            $page->where('averageRating', 5)
        );
    }

    public function test_guest_redirected_to_login(): void
    {
        $review = Review::factory()->create();

        $response = $this->delete("/reviews/{$review->id}");

        $response->assertRedirect(route('auth.login'));
    }

    public function test_review_no_longer_in_book_detail_after_deletion(): void
    {
        $user   = User::factory()->create();
        $book   = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'comment' => 'Gone soon']);

        $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response = $this->actingAs($user)->get("/books/{$book->id}");

        $response->assertInertia(fn ($page) =>
            $page->has('book.reviews', 0)
        );
    }
}
