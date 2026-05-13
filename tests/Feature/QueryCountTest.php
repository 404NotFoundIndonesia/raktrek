<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookImage;
use App\Models\Borrowing;
// BookImage kept for scale test (creates images per book)
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verifies that eager loading is in place and no N+1 queries occur.
 *
 * Thresholds are set generously above the ~6-9 actual queries to absorb
 * framework overhead (session, shared Inertia props, etc.) while still
 * catching N+1 regressions: with 10 books and N+1, query counts would
 * exceed 30+, far above the thresholds.
 */
class QueryCountTest extends TestCase
{
    use RefreshDatabase;

    private function countQueries(callable $fn): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $fn();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    public function test_books_index_query_count_does_not_scale_with_book_count(): void
    {
        $author = Author::factory()->create();
        $genre  = Genre::factory()->create();
        $books  = Book::factory()->count(10)->create(['author_id' => $author->id]);
        $books->each(fn ($b) => $b->genres()->attach($genre->id));

        $count = $this->countQueries(fn () => $this->get('/books'));

        $this->assertLessThanOrEqual(
            12,
            $count,
            "GET /books with 10 books executed {$count} queries — possible N+1"
        );
    }

    public function test_books_index_query_count_constant_with_more_books(): void
    {
        $author = Author::factory()->create();
        $books3 = Book::factory()->count(3)->create(['author_id' => $author->id]);

        $countWith3 = $this->countQueries(fn () => $this->get('/books'));

        Book::factory()->count(10)->create(['author_id' => $author->id]);

        $countWith13 = $this->countQueries(fn () => $this->get('/books'));

        $this->assertLessThanOrEqual(
            $countWith3 + 2,
            $countWith13,
            "Query count grew from {$countWith3} to {$countWith13} — likely N+1"
        );
    }

    public function test_book_show_query_count_does_not_scale_with_reviews(): void
    {
        $author = Author::factory()->create();
        $book   = Book::factory()->create(['author_id' => $author->id]);
        $users  = User::factory()->count(8)->create();
        foreach ($users as $i => $u) {
            $book->reviews()->create(['user_id' => $u->id, 'rate' => ($i % 5) + 1]);
        }

        $count = $this->countQueries(fn () => $this->get("/books/{$book->id}"));

        $this->assertLessThanOrEqual(
            15,
            $count,
            "GET /books/{$book->id} with 8 reviews executed {$count} queries — possible N+1"
        );
    }

    public function test_my_borrowings_query_count_does_not_scale_with_borrow_count(): void
    {
        $member = User::factory()->create();
        $author = Author::factory()->create();
        $books  = Book::factory()->count(8)->create(['author_id' => $author->id]);
        foreach ($books as $book) {
            Borrowing::factory()->create(['user_id' => $member->id, 'book_id' => $book->id]);
        }

        $count = $this->countQueries(
            fn () => $this->actingAs($member)->get('/my/borrowings')
        );

        $this->assertLessThanOrEqual(
            10,
            $count,
            "GET /my/borrowings with 8 borrowings executed {$count} queries — possible N+1"
        );
    }

    public function test_books_index_query_count_fixed_regardless_of_scale(): void
    {
        $author  = Author::factory()->create();
        $books10 = Book::factory()->count(10)->create(['author_id' => $author->id]);
        foreach ($books10 as $book) {
            BookImage::factory()->create(['book_id' => $book->id]);
        }

        $count10 = $this->countQueries(fn () => $this->get('/books'));

        Book::factory()->count(20)->create(['author_id' => $author->id]);

        $count30 = $this->countQueries(fn () => $this->get('/books'));

        $this->assertLessThanOrEqual(
            $count10 + 2,
            $count30,
            "Query count grew from {$count10} (10 books) to {$count30} (30 books) — N+1 detected"
        );
    }
}
