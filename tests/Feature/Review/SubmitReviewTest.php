<?php

namespace Tests\Feature\Review;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_submit_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rate'    => 4,
            'comment' => 'Great book!',
        ]);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rate'    => 4,
            'comment' => 'Great book!',
        ]);
    }

    public function test_comment_is_optional(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", ['rate' => 3]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'book_id' => $book->id]);
    }

    public function test_rate_0_returns_422(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", ['rate' => 0]);

        $response->assertSessionHasErrors('rate');
    }

    public function test_rate_6_returns_422(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", ['rate' => 6]);

        $response->assertSessionHasErrors('rate');
    }

    public function test_duplicate_review_returns_422(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rate' => 3]);

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", ['rate' => 4]);

        $response->assertSessionHasErrors('rate');
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_guest_redirected_to_login(): void
    {
        $book = Book::factory()->create();

        $response = $this->post("/books/{$book->id}/reviews", ['rate' => 4]);

        $response->assertRedirect(route('auth.login'));
    }

    public function test_average_rating_recalculated_after_submission(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book  = Book::factory()->create();

        Review::factory()->create(['user_id' => $user1->id, 'book_id' => $book->id, 'rate' => 4]);

        $this->actingAs($user2)->post("/books/{$book->id}/reviews", ['rate' => 2]);

        $response = $this->actingAs($user2)->get("/books/{$book->id}");

        $response->assertInertia(fn ($page) =>
            $page->where('averageRating', 3)
        );
    }

    public function test_rate_is_required(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", ['comment' => 'no rate']);

        $response->assertSessionHasErrors('rate');
    }
}
