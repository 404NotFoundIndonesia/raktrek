<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_reports(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get('/admin/reports');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Reports/Index')
                 ->has('mostBorrowed')
                 ->has('overdueCount')
                 ->has('overdueBorrowings')
                 ->has('activeMembers')
        );
    }

    public function test_overdue_count_is_accurate(): void
    {
        $staff = User::factory()->staff()->create();
        Borrowing::factory()->create(['due_date' => now()->subDays(3)]);
        Borrowing::factory()->create(['due_date' => now()->subDays(1)]);
        Borrowing::factory()->create(['due_date' => now()->addDays(5)]);
        Borrowing::factory()->returned()->create(['due_date' => now()->subDays(2)]);

        $response = $this->actingAs($staff)->get('/admin/reports');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('overdueCount', 2)
        );
    }

    public function test_most_borrowed_orders_by_count_desc(): void
    {
        $staff   = User::factory()->staff()->create();
        $popular = Book::factory()->create(['title' => 'Popular']);
        $other   = Book::factory()->create(['title' => 'Other']);

        Borrowing::factory()->count(3)->create(['book_id' => $popular->id]);
        Borrowing::factory()->count(1)->create(['book_id' => $other->id]);

        $response = $this->actingAs($staff)->get('/admin/reports');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('mostBorrowed.0.id', $popular->id)
        );
    }

    public function test_overdue_borrowings_list_not_include_returned(): void
    {
        $staff = User::factory()->staff()->create();
        Borrowing::factory()->create(['due_date' => now()->subDays(2)]);
        Borrowing::factory()->returned()->create(['due_date' => now()->subDays(5)]);

        $response = $this->actingAs($staff)->get('/admin/reports');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('overdueBorrowings', 1)
        );
    }

    public function test_member_cannot_access_reports(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get('/admin/reports')->assertForbidden();
    }
}
