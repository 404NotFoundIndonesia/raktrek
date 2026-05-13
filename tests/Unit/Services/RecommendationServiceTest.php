<?php

namespace Tests\Unit\Services;

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecommendationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RecommendationService();
    }

    public function test_guest_receives_top_rated_fallback(): void
    {
        $low  = Book::factory()->create();
        $high = Book::factory()->create();

        Review::factory()->create(['book_id' => $low->id, 'rate' => 1]);
        Review::factory()->create(['book_id' => $high->id, 'rate' => 5]);

        $results = $this->service->forUser(null);

        $this->assertEquals($high->id, $results->first()->id);
    }

    public function test_result_count_never_exceeds_limit(): void
    {
        Book::factory()->count(15)->create();

        $results = $this->service->forUser(null, 10);

        $this->assertLessThanOrEqual(10, $results->count());
    }

    public function test_books_by_favourite_authors_ranked_first(): void
    {
        $user   = User::factory()->create();
        $author = Author::factory()->create();

        $favBook   = Book::factory()->create(['author_id' => $author->id]);
        $otherBook = Book::factory()->create();

        Review::factory()->create(['book_id' => $otherBook->id, 'rate' => 5]);

        $user->favouriteAuthors()->attach($author);

        $results = $this->service->forUser($user);

        $this->assertEquals($favBook->id, $results->first()->id);
    }

    public function test_actively_borrowed_books_excluded(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'return_date' => null]);

        $results = $this->service->forUser($user);

        $this->assertNotContains($book->id, $results->pluck('id')->all());
    }

    public function test_returned_books_not_excluded(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Borrowing::factory()->returned()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $results = $this->service->forUser($user);

        $this->assertContains($book->id, $results->pluck('id')->all());
    }

    public function test_preferred_genre_books_rank_above_unrelated(): void
    {
        $user  = User::factory()->create();
        $genre = Genre::factory()->create();

        $preferredBook = Book::factory()->create();
        $preferredBook->genres()->attach($genre);

        $unrelatedBook = Book::factory()->create();
        Review::factory()->create(['book_id' => $unrelatedBook->id, 'rate' => 5]);

        $pastBook = Book::factory()->create();
        $pastBook->genres()->attach($genre);
        Borrowing::factory()->returned()->create(['user_id' => $user->id, 'book_id' => $pastBook->id]);

        $results = $this->service->forUser($user);

        $ids = $results->pluck('id')->all();
        $preferredPos = array_search($preferredBook->id, $ids);
        $unrelatedPos = array_search($unrelatedBook->id, $ids);

        $this->assertLessThan($unrelatedPos, $preferredPos);
    }

    public function test_new_member_with_no_history_receives_top_rated(): void
    {
        $user = User::factory()->create();

        $low  = Book::factory()->create();
        $high = Book::factory()->create();

        Review::factory()->create(['book_id' => $low->id, 'rate' => 1]);
        Review::factory()->create(['book_id' => $high->id, 'rate' => 5]);

        $results = $this->service->forUser($user);

        $this->assertEquals($high->id, $results->first()->id);
    }

    public function test_results_have_no_duplicates(): void
    {
        $user   = User::factory()->create();
        $author = Author::factory()->create();
        $genre  = Genre::factory()->create();

        $book = Book::factory()->create(['author_id' => $author->id]);
        $book->genres()->attach($genre);

        $user->favouriteAuthors()->attach($author);

        $pastBook = Book::factory()->create();
        $pastBook->genres()->attach($genre);
        Borrowing::factory()->returned()->create(['user_id' => $user->id, 'book_id' => $pastBook->id]);

        $results = $this->service->forUser($user);

        $this->assertEquals($results->count(), $results->unique('id')->count());
    }
}
