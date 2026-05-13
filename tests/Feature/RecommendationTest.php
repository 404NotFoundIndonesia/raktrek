<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_home_contains_recommendations_prop(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Home')->has('recommendations')
        );
    }

    public function test_guest_home_contains_popular_fallback_recommendations(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Home')->has('recommendations')
        );
    }

    public function test_recommendations_include_book_author_and_image_data(): void
    {
        $book = Book::factory()->create();
        Review::factory()->create(['book_id' => $book->id, 'rate' => 5]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('recommendations.0.id')
        );
    }

    public function test_member_recommendations_capped_at_10(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(15)->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('recommendations', 10)
        );
    }
}
