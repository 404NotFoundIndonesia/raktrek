<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\User;
use App\Models\WaitingList;
use App\Notifications\BookAvailableNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminWaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_book_queue_ordered_by_created_at(): void
    {
        $staff = User::factory()->staff()->create();
        $book  = Book::factory()->create(['availability' => 0]);

        $entry1 = WaitingList::factory()->create(['book_id' => $book->id, 'created_at' => now()->subMinutes(10)]);
        $entry2 = WaitingList::factory()->create(['book_id' => $book->id, 'created_at' => now()->subMinutes(5)]);
        $entry3 = WaitingList::factory()->create(['book_id' => $book->id, 'created_at' => now()]);

        $response = $this->actingAs($staff)->get("/admin/books/{$book->id}/waitlists");

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Waitlists/Index')
                 ->has('queue', 3)
                 ->where('queue.0.id', $entry1->id)
                 ->where('queue.1.id', $entry2->id)
                 ->where('queue.2.id', $entry3->id)
        );
    }

    public function test_queue_position_indexed_from_1(): void
    {
        $staff = User::factory()->staff()->create();
        $book  = Book::factory()->create(['availability' => 0]);

        WaitingList::factory()->count(3)->create(['book_id' => $book->id]);

        $response = $this->actingAs($staff)->get("/admin/books/{$book->id}/waitlists");

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('queue.0.queue_position', 1)
                 ->where('queue.1.queue_position', 2)
                 ->where('queue.2.queue_position', 3)
        );
    }

    public function test_staff_can_remove_any_waitlist_entry(): void
    {
        $staff = User::factory()->staff()->create();
        $entry = WaitingList::factory()->create();

        $response = $this->actingAs($staff)->delete("/admin/waitlists/{$entry->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('waiting_lists', ['id' => $entry->id]);
    }

    public function test_member_on_admin_waitlist_route_is_forbidden(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $book   = Book::factory()->create();

        $response = $this->actingAs($member)->get("/admin/books/{$book->id}/waitlists");

        $response->assertForbidden();
    }

    public function test_member_cannot_use_admin_delete_route(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $entry  = WaitingList::factory()->create();

        $response = $this->actingAs($member)->delete("/admin/waitlists/{$entry->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('waiting_lists', ['id' => $entry->id]);
    }

    public function test_guest_redirected_to_login_on_admin_routes(): void
    {
        $book = Book::factory()->create();

        $this->get("/admin/books/{$book->id}/waitlists")->assertRedirect(route('auth.login'));
    }

    public function test_removing_first_in_queue_notifies_next_person(): void
    {
        Notification::fake();

        $staff  = User::factory()->staff()->create();
        $book   = Book::factory()->create(['availability' => 0]);
        $first  = User::factory()->create();
        $second = User::factory()->create();

        $entry1 = WaitingList::factory()->create([
            'book_id' => $book->id,
            'user_id' => $first->id,
            'created_at' => now()->subMinutes(10),
        ]);
        WaitingList::factory()->create([
            'book_id' => $book->id,
            'user_id' => $second->id,
            'created_at' => now(),
        ]);

        $this->actingAs($staff)->delete("/admin/waitlists/{$entry1->id}");

        Notification::assertSentTo($second, BookAvailableNotification::class);
        Notification::assertNotSentTo($first, BookAvailableNotification::class);
    }

    public function test_removing_non_first_in_queue_does_not_notify(): void
    {
        Notification::fake();

        $staff  = User::factory()->staff()->create();
        $book   = Book::factory()->create(['availability' => 0]);
        $first  = User::factory()->create();
        $second = User::factory()->create();

        WaitingList::factory()->create([
            'book_id' => $book->id,
            'user_id' => $first->id,
            'created_at' => now()->subMinutes(10),
        ]);
        $entry2 = WaitingList::factory()->create([
            'book_id' => $book->id,
            'user_id' => $second->id,
            'created_at' => now(),
        ]);

        $this->actingAs($staff)->delete("/admin/waitlists/{$entry2->id}");

        Notification::assertNothingSent();
    }
}
