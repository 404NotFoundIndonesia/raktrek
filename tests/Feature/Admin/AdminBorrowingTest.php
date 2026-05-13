<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBorrowingTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_list_all_borrowings(): void
    {
        $staff = User::factory()->staff()->create();
        Borrowing::factory()->count(3)->create();

        $response = $this->actingAs($staff)->get('/admin/borrowings');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Borrowings/Index')->has('borrowings.data', 3)
        );
    }

    public function test_filter_active_returns_only_active_borrowings(): void
    {
        $staff   = User::factory()->staff()->create();
        $active  = Borrowing::factory()->create(['due_date' => now()->addDays(5)]);
        $overdue = Borrowing::factory()->create(['due_date' => now()->subDays(2)]);
        Borrowing::factory()->returned()->create();

        $response = $this->actingAs($staff)->get('/admin/borrowings?filter=active');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('borrowings.data', 1)
                 ->where('borrowings.data.0.id', $active->id)
        );
    }

    public function test_filter_overdue_returns_only_overdue_borrowings(): void
    {
        $staff   = User::factory()->staff()->create();
        $active  = Borrowing::factory()->create(['due_date' => now()->addDays(5)]);
        $overdue = Borrowing::factory()->create(['due_date' => now()->subDays(2)]);
        Borrowing::factory()->returned()->create();

        $response = $this->actingAs($staff)->get('/admin/borrowings?filter=overdue');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('borrowings.data', 1)
                 ->where('borrowings.data.0.id', $overdue->id)
        );
    }

    public function test_filter_returned_returns_only_returned_borrowings(): void
    {
        $staff    = User::factory()->staff()->create();
        $active   = Borrowing::factory()->create(['due_date' => now()->addDays(5)]);
        $returned = Borrowing::factory()->returned()->create();

        $response = $this->actingAs($staff)->get('/admin/borrowings?filter=returned');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('borrowings.data', 1)
                 ->where('borrowings.data.0.id', $returned->id)
        );
    }

    public function test_member_cannot_access_admin_borrowings(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get('/admin/borrowings')->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/admin/borrowings')->assertRedirect(route('auth.login'));
    }
}
