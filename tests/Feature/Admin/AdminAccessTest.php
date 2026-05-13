<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_access_admin_dashboard(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get('/admin');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
    }

    public function test_member_cannot_access_admin_dashboard(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($member)->get('/admin');

        $response->assertForbidden();
    }

    public function test_guest_redirected_to_login_for_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('auth.login'));
    }

    public function test_dashboard_returns_stats(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get('/admin');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Dashboard')
                 ->has('stats.total_books')
                 ->has('stats.active_borrowings')
                 ->has('stats.overdue_count')
                 ->has('stats.total_members')
        );
    }

    public function test_member_cannot_access_admin_books(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get('/admin/books')->assertForbidden();
    }

    public function test_guest_cannot_access_admin_reports(): void
    {
        $this->get('/admin/reports')->assertRedirect(route('auth.login'));
    }
}
