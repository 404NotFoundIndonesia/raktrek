<?php

namespace Tests\Unit\Models;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookImage;
use App\Models\Borrowing;
use App\Models\Genre;
use App\Models\Review;
use App\Models\WaitingList;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_uses_soft_deletes(): void
    {
        $this->assertContains(SoftDeletes::class, class_uses_recursive(Book::class));
    }

    public function test_author_relation_is_belongs_to(): void
    {
        $relation = (new Book())->author();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Author::class, $relation->getRelated());
    }

    public function test_genres_relation_is_belongs_to_many_via_book_genre_pivot(): void
    {
        $relation = (new Book())->genres();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertInstanceOf(Genre::class, $relation->getRelated());
        $this->assertEquals('book_genre', $relation->getTable());
    }

    public function test_images_relation_is_has_many(): void
    {
        $relation = (new Book())->images();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(BookImage::class, $relation->getRelated());
    }

    public function test_reviews_relation_is_has_many(): void
    {
        $relation = (new Book())->reviews();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Review::class, $relation->getRelated());
    }

    public function test_reservations_relation_is_has_many(): void
    {
        $relation = (new Book())->reservations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(WaitingList::class, $relation->getRelated());
    }

    public function test_histories_relation_is_has_many(): void
    {
        $relation = (new Book())->histories();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Borrowing::class, $relation->getRelated());
    }

    public function test_soft_deleted_book_excluded_from_default_query(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $book->delete();

        $this->assertNull(Book::find($book->id));
        $this->assertNotNull(Book::withTrashed()->find($book->id));
    }
}
