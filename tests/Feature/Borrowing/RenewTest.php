<?php

namespace Tests\Feature\Borrowing;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RenewTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_renew_when_no_waitlist(): void
    {
        $user     = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create([
            'user_id'  => $user->id,
            'book_id'  => $book->id,
            'due_date' => now()->addDays(3),
        ]);

        $response = $this->actingAs($user)->patch("/borrowings/{$borrowing->id}/renew");

        $response->assertRedirect();

        $newDueDate = $borrowing->fresh()->due_date;
        $expectedDate = now()->addDays(3 + 14)->startOfDay();

        $this->assertTrue($expectedDate->eq($newDueDate->startOfDay()));
    }

    public function test_renew_blocked_when_waitlist_exists(): void
    {
        $user     = User::factory()->create();
        $waiter   = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);
        WaitingList::factory()->create(['user_id' => $waiter->id, 'book_id' => $book->id]);

        $originalDueDate = $borrowing->due_date;

        $response = $this->actingAs($user)->patch("/borrowings/{$borrowing->id}/renew");

        $response->assertSessionHasErrors('borrowing');
        $this->assertEquals($originalDueDate, $borrowing->fresh()->due_date);
    }

    public function test_non_owner_cannot_renew(): void
    {
        $owner    = User::factory()->create();
        $other    = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $borrowing = Borrowing::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

        $response = $this->actingAs($other)->patch("/borrowings/{$borrowing->id}/renew");

        $response->assertForbidden();
    }

    public function test_due_date_extended_by_exactly_14_days(): void
    {
        $user     = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $original = now()->addDays(5);
        $borrowing = Borrowing::factory()->create([
            'user_id'  => $user->id,
            'book_id'  => $book->id,
            'due_date' => $original,
        ]);

        $this->actingAs($user)->patch("/borrowings/{$borrowing->id}/renew");

        $expected = $original->copy()->addDays(14)->startOfDay();
        $this->assertTrue($expected->eq($borrowing->fresh()->due_date->startOfDay()));
    }
}
