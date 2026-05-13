<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Home')
                 ->has('recommendations')
        );
    }

    public function test_guest_receives_top_rated_books(): void
    {
        $low  = Book::factory()->create();
        $high = Book::factory()->create();

        Review::factory()->create(['book_id' => $low->id, 'rate' => 2]);
        Review::factory()->create(['book_id' => $high->id, 'rate' => 5]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('recommendations.0.id', $high->id)
        );
    }

    public function test_member_with_favourite_authors_receives_their_books(): void
    {
        $user   = User::factory()->create();
        $author = Author::factory()->create();
        $book   = Book::factory()->create(['author_id' => $author->id]);

        $user->favouriteAuthors()->attach($author);

        $otherBook = Book::factory()->create();
        Review::factory()->create(['book_id' => $otherBook->id, 'rate' => 5]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('recommendations.0.id', $book->id)
        );
    }

    public function test_member_with_no_favourites_receives_top_rated_fallback(): void
    {
        $user = User::factory()->create();

        $low  = Book::factory()->create();
        $high = Book::factory()->create();

        Review::factory()->create(['book_id' => $low->id, 'rate' => 1]);
        Review::factory()->create(['book_id' => $high->id, 'rate' => 5]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('recommendations.0.id', $high->id)
        );
    }

    public function test_recommendations_capped_at_10(): void
    {
        Book::factory()->count(15)->create();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('recommendations', 10)
        );
    }
}
