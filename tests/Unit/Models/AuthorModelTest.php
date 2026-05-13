<?php

namespace Tests\Unit\Models;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_uses_soft_deletes(): void
    {
        $this->assertContains(SoftDeletes::class, class_uses_recursive(Author::class));
    }

    public function test_books_relation_is_has_many(): void
    {
        $relation = (new Author())->books();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Book::class, $relation->getRelated());
    }

    public function test_followers_relation_is_belongs_to_many_via_favourite_author_pivot(): void
    {
        $relation = (new Author())->followers();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
        $this->assertEquals('favourite_author', $relation->getTable());
    }

    public function test_soft_deleted_author_excluded_from_default_query(): void
    {
        $author = Author::factory()->create();
        $author->delete();

        $this->assertNull(Author::find($author->id));
        $this->assertNotNull(Author::withTrashed()->find($author->id));
    }
}
