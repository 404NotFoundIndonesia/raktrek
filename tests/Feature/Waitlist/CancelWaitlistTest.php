<?php

namespace Tests\Feature\Waitlist;

use App\Models\Book;
use App\Models\User;
use App\Models\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelWaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_cancel_own_entry(): void
    {
        $user  = User::factory()->create();
        $book  = Book::factory()->create(['availability' => 0]);
        $entry = WaitingList::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->delete("/waitlists/{$entry->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('waiting_lists', ['id' => $entry->id]);
    }

    public function test_non_owner_member_cannot_cancel(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book  = Book::factory()->create(['availability' => 0]);
        $entry = WaitingList::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

        $response = $this->actingAs($other)->delete("/waitlists/{$entry->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('waiting_lists', ['id' => $entry->id]);
    }

    public function test_staff_can_cancel_any_entry(): void
    {
        $staff = User::factory()->staff()->create();
        $owner = User::factory()->create();
        $book  = Book::factory()->create(['availability' => 0]);
        $entry = WaitingList::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

        $response = $this->actingAs($staff)->delete("/waitlists/{$entry->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('waiting_lists', ['id' => $entry->id]);
    }

    public function test_other_queue_positions_unaffected_after_deletion(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $book  = Book::factory()->create(['availability' => 0]);

        $entry1 = WaitingList::factory()->create(['user_id' => $user1->id, 'book_id' => $book->id]);
        $entry2 = WaitingList::factory()->create(['user_id' => $user2->id, 'book_id' => $book->id]);
        $entry3 = WaitingList::factory()->create(['user_id' => $user3->id, 'book_id' => $book->id]);

        $this->actingAs($user1)->delete("/waitlists/{$entry1->id}");

        $this->assertDatabaseHas('waiting_lists', ['id' => $entry2->id]);
        $this->assertDatabaseHas('waiting_lists', ['id' => $entry3->id]);
        $this->assertDatabaseCount('waiting_lists', 2);
    }

    public function test_guest_redirected_to_login(): void
    {
        $entry = WaitingList::factory()->create();

        $response = $this->delete("/waitlists/{$entry->id}");

        $response->assertRedirect(route('auth.login'));
    }
}
