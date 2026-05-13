<?php

namespace Tests\Feature\Admin;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_list_authors(): void
    {
        $staff = User::factory()->staff()->create();
        Author::factory()->count(3)->create();

        $response = $this->actingAs($staff)->get('/admin/authors');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Authors/Index')->has('authors.data', 3)
        );
    }

    public function test_staff_can_create_author(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->post('/admin/authors', [
            'name'  => 'New Author',
            'about' => 'About text',
        ]);

        $response->assertRedirect('/admin/authors');
        $this->assertDatabaseHas('authors', ['name' => 'New Author']);
    }

    public function test_create_author_requires_name(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->post('/admin/authors', []);

        $response->assertSessionHasErrors('name');
    }

    public function test_staff_can_update_author(): void
    {
        $staff  = User::factory()->staff()->create();
        $author = Author::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($staff)->put("/admin/authors/{$author->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect('/admin/authors');
        $this->assertEquals('Updated Name', $author->fresh()->name);
    }

    public function test_staff_can_delete_author_without_books(): void
    {
        $staff  = User::factory()->staff()->create();
        $author = Author::factory()->create();

        $response = $this->actingAs($staff)->delete("/admin/authors/{$author->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('authors', ['id' => $author->id]);
    }

    public function test_cannot_delete_author_with_books(): void
    {
        $staff  = User::factory()->staff()->create();
        $author = Author::factory()->create();
        Book::factory()->create(['author_id' => $author->id]);

        $response = $this->actingAs($staff)->delete("/admin/authors/{$author->id}");

        $response->assertSessionHasErrors('author');
        $this->assertDatabaseHas('authors', ['id' => $author->id]);
    }

    public function test_member_cannot_access_admin_authors(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get('/admin/authors')->assertForbidden();
    }
}
