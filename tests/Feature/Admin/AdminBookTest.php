<?php

namespace Tests\Feature\Admin;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use App\Notifications\NewBookByFavouriteAuthorNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminBookTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_list_books(): void
    {
        $staff = User::factory()->staff()->create();
        Book::factory()->count(3)->create();

        $response = $this->actingAs($staff)->get('/admin/books');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Books/Index')->has('books.data', 3)
        );
    }

    public function test_staff_can_view_create_form(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get('/admin/books/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Books/Create')->has('authors')->has('genres')
        );
    }

    public function test_staff_can_create_book(): void
    {
        $staff  = User::factory()->staff()->create();
        $author = Author::factory()->create();

        $response = $this->actingAs($staff)->post('/admin/books', [
            'title'     => 'Test Book',
            'author_id' => $author->id,
        ]);

        $response->assertRedirect('/admin/books');
        $this->assertDatabaseHas('books', ['title' => 'Test Book']);
    }

    public function test_create_book_notifies_fans_of_author(): void
    {
        Notification::fake();

        $staff  = User::factory()->staff()->create();
        $author = Author::factory()->create();
        $fan    = User::factory()->create();
        $fan->favouriteAuthors()->attach($author->id);

        $this->actingAs($staff)->post('/admin/books', [
            'title'     => 'New Fan Book',
            'author_id' => $author->id,
        ]);

        Notification::assertSentTo($fan, NewBookByFavouriteAuthorNotification::class);
    }

    public function test_create_book_requires_title(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->post('/admin/books', []);

        $response->assertSessionHasErrors('title');
    }

    public function test_staff_can_update_book(): void
    {
        $staff  = User::factory()->staff()->create();
        $author = Author::factory()->create();
        $book   = Book::factory()->create(['title' => 'Old Title', 'author_id' => $author->id]);

        $response = $this->actingAs($staff)->put("/admin/books/{$book->id}", [
            'title'     => 'New Title',
            'author_id' => $author->id,
        ]);

        $response->assertRedirect('/admin/books');
        $this->assertEquals('New Title', $book->fresh()->title);
    }

    public function test_staff_can_soft_delete_book(): void
    {
        $staff = User::factory()->staff()->create();
        $book  = Book::factory()->create();

        $response = $this->actingAs($staff)->delete("/admin/books/{$book->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('books', ['id' => $book->id]);
    }

    public function test_soft_deleted_book_appears_in_admin_listing(): void
    {
        $staff = User::factory()->staff()->create();
        $book  = Book::factory()->create();
        $book->delete();

        $response = $this->actingAs($staff)->get('/admin/books');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('books.data', 1));
    }

    public function test_staff_can_sync_genres_on_create(): void
    {
        $staff  = User::factory()->staff()->create();
        $author = Author::factory()->create();
        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $this->actingAs($staff)->post('/admin/books', [
            'title'     => 'Genre Book',
            'author_id' => $author->id,
            'genres'    => [$genre1->id, $genre2->id],
        ]);

        $book = Book::where('title', 'Genre Book')->firstOrFail();
        $this->assertCount(2, $book->genres);
    }
}
