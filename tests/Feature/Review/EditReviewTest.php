<?php

namespace Tests\Feature\Review;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_rate_and_comment(): void
    {
        $user   = User::factory()->create();
        $book   = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rate' => 3, 'comment' => 'Okay']);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
            'rate'    => 5,
            'comment' => 'Amazing!',
        ]);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'id'      => $review->id,
            'rate'    => 5,
            'comment' => 'Amazing!',
        ]);
    }

    public function test_non_owner_cannot_update(): void
    {
        $owner  = User::factory()->create();
        $other  = User::factory()->create();
        $book   = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id, 'rate' => 3]);

        $response = $this->actingAs($other)->put("/reviews/{$review->id}", ['rate' => 5]);

        $response->assertForbidden();
        $this->assertEquals(3, $review->fresh()->rate);
    }

    public function test_updated_rate_changes_average_rating(): void
    {
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $book   = Book::factory()->create();

        Review::factory()->create(['user_id' => $user1->id, 'book_id' => $book->id, 'rate' => 2]);
        $review = Review::factory()->create(['user_id' => $user2->id, 'book_id' => $book->id, 'rate' => 2]);

        $this->actingAs($user2)->put("/reviews/{$review->id}", ['rate' => 4]);

        $response = $this->actingAs($user2)->get("/books/{$book->id}");

        $response->assertInertia(fn ($page) =>
            $page->where('averageRating', 3)
        );
    }

    public function test_rate_must_be_1_to_5(): void
    {
        $user   = User::factory()->create();
        $book   = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rate' => 3]);

        $this->actingAs($user)->put("/reviews/{$review->id}", ['rate' => 0])->assertSessionHasErrors('rate');
        $this->actingAs($user)->put("/reviews/{$review->id}", ['rate' => 6])->assertSessionHasErrors('rate');
    }

    public function test_guest_redirected_to_login(): void
    {
        $review = Review::factory()->create();

        $response = $this->put("/reviews/{$review->id}", ['rate' => 5]);

        $response->assertRedirect(route('auth.login'));
    }
}
