<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_list_genres(): void
    {
        $staff = User::factory()->staff()->create();
        Genre::factory()->count(4)->create();

        $response = $this->actingAs($staff)->get('/admin/genres');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Genres/Index')->has('genres.data', 4)
        );
    }

    public function test_staff_can_create_genre(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->post('/admin/genres', [
            'name'        => 'Science Fiction',
            'description' => 'Sci-fi books',
        ]);

        $response->assertRedirect('/admin/genres');
        $this->assertDatabaseHas('genres', ['name' => 'Science Fiction']);
    }

    public function test_duplicate_genre_name_rejected(): void
    {
        $staff = User::factory()->staff()->create();
        Genre::factory()->create(['name' => 'Fiction']);

        $response = $this->actingAs($staff)->post('/admin/genres', ['name' => 'Fiction']);

        $response->assertSessionHasErrors('name');
    }

    public function test_staff_can_update_genre(): void
    {
        $staff = User::factory()->staff()->create();
        $genre = Genre::factory()->create(['name' => 'Old Genre']);

        $response = $this->actingAs($staff)->put("/admin/genres/{$genre->id}", [
            'name' => 'New Genre',
        ]);

        $response->assertRedirect('/admin/genres');
        $this->assertEquals('New Genre', $genre->fresh()->name);
    }

    public function test_update_genre_allows_same_name(): void
    {
        $staff = User::factory()->staff()->create();
        $genre = Genre::factory()->create(['name' => 'Fantasy']);

        $response = $this->actingAs($staff)->put("/admin/genres/{$genre->id}", [
            'name' => 'Fantasy',
        ]);

        $response->assertRedirect('/admin/genres');
    }

    public function test_staff_can_delete_empty_genre(): void
    {
        $staff = User::factory()->staff()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($staff)->delete("/admin/genres/{$genre->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_cannot_delete_genre_assigned_to_books(): void
    {
        $staff = User::factory()->staff()->create();
        $genre = Genre::factory()->create();
        $book  = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($staff)->delete("/admin/genres/{$genre->id}");

        $response->assertSessionHasErrors('genre');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    public function test_member_cannot_access_admin_genres(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get('/admin/genres')->assertForbidden();
    }
}
