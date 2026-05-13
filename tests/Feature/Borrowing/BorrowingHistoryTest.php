<?php

namespace Tests\Feature\Borrowing;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_borrowings_returns_only_own_records(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        Borrowing::factory()->count(3)->create(['user_id' => $user->id]);
        Borrowing::factory()->count(2)->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->get('/my/borrowings');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Borrowings/Index')
                 ->has('borrowings.data', 3)
        );
    }

    public function test_overdue_borrowing_has_is_overdue_true(): void
    {
        $user     = User::factory()->create();
        $book     = Book::factory()->create(['availability' => 0]);
        $overdue  = Borrowing::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'due_date'    => now()->subDays(3),
            'return_date' => null,
        ]);

        $response = $this->actingAs($user)->get('/my/borrowings');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('borrowings.data.0', fn ($item) =>
                $item->where('is_overdue', true)->etc()
            )
        );
    }

    public function test_active_borrowing_is_not_overdue(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Borrowing::factory()->create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'due_date'    => now()->addDays(7),
            'return_date' => null,
        ]);

        $response = $this->actingAs($user)->get('/my/borrowings');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('borrowings.data.0', fn ($item) =>
                $item->where('is_overdue', false)->etc()
            )
        );
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/my/borrowings');

        $response->assertRedirect(route('auth.login'));
    }

    public function test_page_accessible_to_member(): void
    {
        $user = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($user)->get('/my/borrowings');

        $response->assertOk();
    }

    public function test_page_accessible_to_staff(): void
    {
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->get('/my/borrowings');

        $response->assertOk();
    }
}
