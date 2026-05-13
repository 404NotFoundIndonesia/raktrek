<?php

namespace Tests\Feature\Author;

use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavouriteAuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_favourite_an_author(): void
    {
        $user   = User::factory()->create();
        $author = Author::factory()->create();

        $response = $this->actingAs($user)->post("/authors/{$author->id}/favourite");

        $response->assertRedirect();
        $this->assertDatabaseHas('favourite_author', [
            'user_id'   => $user->id,
            'author_id' => $author->id,
        ]);
    }

    public function test_favouriting_same_author_twice_is_idempotent(): void
    {
        $user   = User::factory()->create();
        $author = Author::factory()->create();

        $this->actingAs($user)->post("/authors/{$author->id}/favourite");
        $this->actingAs($user)->post("/authors/{$author->id}/favourite");

        $this->assertDatabaseCount('favourite_author', 1);
    }

    public function test_member_can_unfavourite_an_author(): void
    {
        $user   = User::factory()->create();
        $author = Author::factory()->create();

        $user->favouriteAuthors()->attach($author);

        $response = $this->actingAs($user)->delete("/authors/{$author->id}/favourite");

        $response->assertRedirect();
        $this->assertDatabaseMissing('favourite_author', [
            'user_id'   => $user->id,
            'author_id' => $author->id,
        ]);
    }

    public function test_unfavouriting_author_not_in_favourites_is_safe(): void
    {
        $user   = User::factory()->create();
        $author = Author::factory()->create();

        $response = $this->actingAs($user)->delete("/authors/{$author->id}/favourite");

        $response->assertRedirect();
        $this->assertDatabaseCount('favourite_author', 0);
    }

    public function test_guest_redirected_to_login_on_favourite(): void
    {
        $author = Author::factory()->create();

        $response = $this->post("/authors/{$author->id}/favourite");

        $response->assertRedirect(route('auth.login'));
    }

    public function test_guest_redirected_to_login_on_unfavourite(): void
    {
        $author = Author::factory()->create();

        $response = $this->delete("/authors/{$author->id}/favourite");

        $response->assertRedirect(route('auth.login'));
    }

    public function test_favourite_does_not_affect_other_users(): void
    {
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $author = Author::factory()->create();

        $this->actingAs($user1)->post("/authors/{$author->id}/favourite");

        $this->assertDatabaseMissing('favourite_author', [
            'user_id'   => $user2->id,
            'author_id' => $author->id,
        ]);
    }
}
