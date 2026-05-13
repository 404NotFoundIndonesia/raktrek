<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\WaitingList;
use App\Notifications\BookAvailableNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_return_any_member_borrowing(): void
    {
        $staff    = User::factory()->staff()->create();
        $member   = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $member->id, 'book_id' => $book->id]);

        $response = $this->actingAs($staff)->patch("/admin/borrowings/{$borrowing->id}/return");

        $response->assertRedirect();
        $this->assertNotNull($borrowing->fresh()->return_date);
        $this->assertEquals(1, $book->fresh()->availability);
    }

    public function test_member_on_admin_return_route_is_forbidden(): void
    {
        $member   = User::factory()->create(['role' => 'member']);
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $member->id, 'book_id' => $book->id]);

        $response = $this->actingAs($member)->patch("/admin/borrowings/{$borrowing->id}/return");

        $response->assertForbidden();
        $this->assertNull($borrowing->fresh()->return_date);
    }

    public function test_guest_redirected_to_login_on_admin_return(): void
    {
        $book     = Book::factory()->create(['availability' => 0]);
        $member   = User::factory()->create();
        $borrowing = Borrowing::factory()->create(['user_id' => $member->id, 'book_id' => $book->id]);

        $response = $this->patch("/admin/borrowings/{$borrowing->id}/return");

        $response->assertRedirect(route('auth.login'));
    }

    public function test_admin_return_triggers_waitlist_notification(): void
    {
        Notification::fake();

        $staff    = User::factory()->staff()->create();
        $member   = User::factory()->create();
        $waiter   = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $member->id, 'book_id' => $book->id]);
        WaitingList::factory()->create(['user_id' => $waiter->id, 'book_id' => $book->id]);

        $this->actingAs($staff)->patch("/admin/borrowings/{$borrowing->id}/return");

        Notification::assertSentTo($waiter, BookAvailableNotification::class);
    }

    public function test_admin_return_already_returned_returns_error(): void
    {
        $staff    = User::factory()->staff()->create();
        $member   = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 1]);
        $borrowing = Borrowing::factory()->returned()->create(['user_id' => $member->id, 'book_id' => $book->id]);

        $response = $this->actingAs($staff)->patch("/admin/borrowings/{$borrowing->id}/return");

        $response->assertSessionHasErrors('borrowing');
    }
}
