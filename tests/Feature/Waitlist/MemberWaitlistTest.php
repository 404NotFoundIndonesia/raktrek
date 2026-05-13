<?php

namespace Tests\Feature\Waitlist;

use App\Models\Book;
use App\Models\User;
use App\Models\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberWaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_waitlists_returns_only_own_entries(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        WaitingList::factory()->count(2)->create(['user_id' => $user->id]);
        WaitingList::factory()->count(3)->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->get('/my/waitlists');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Waitlists/Index')
                 ->has('waitlists', 2)
        );
    }

    public function test_queue_position_is_correct(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book  = Book::factory()->create(['availability' => 0]);

        WaitingList::factory()->create(['user_id' => $user1->id, 'book_id' => $book->id]);
        WaitingList::factory()->create(['user_id' => $user2->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user2)->get('/my/waitlists');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('waitlists.0.queue_position', 2)
        );
    }

    public function test_expired_entry_has_is_expired_true(): void
    {
        $user  = User::factory()->create();
        $book  = Book::factory()->create(['availability' => 0]);

        WaitingList::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'finish_date' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get('/my/waitlists');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('waitlists.0', fn ($item) =>
                $item->where('is_expired', true)->etc()
            )
        );
    }

    public function test_active_entry_has_is_expired_false(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 0]);

        WaitingList::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'finish_date' => now()->addDays(5),
        ]);

        $response = $this->actingAs($user)->get('/my/waitlists');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('waitlists.0', fn ($item) =>
                $item->where('is_expired', false)->etc()
            )
        );
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/my/waitlists');

        $response->assertRedirect(route('auth.login'));
    }

    public function test_first_position_in_queue_for_single_entry(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['availability' => 0]);

        WaitingList::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->get('/my/waitlists');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('waitlists.0.queue_position', 1)
        );
    }
}
