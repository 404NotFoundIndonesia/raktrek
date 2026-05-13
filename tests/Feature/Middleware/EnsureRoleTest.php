<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'role:staff'])->get('/test-staff-only', fn () => 'ok');
        Route::middleware(['web', 'role:member'])->get('/test-member-only', fn () => 'ok');
        Route::middleware(['web', 'role:member,staff'])->get('/test-member-or-staff', fn () => 'ok');
    }

    public function test_guest_on_staff_route_redirects_to_login(): void
    {
        $response = $this->get('/test-staff-only');

        $response->assertRedirect(route('auth.login'));
    }

    public function test_guest_on_member_route_redirects_to_login(): void
    {
        $response = $this->get('/test-member-only');

        $response->assertRedirect(route('auth.login'));
    }

    public function test_member_on_staff_route_is_forbidden(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->get('/test-staff-only');

        $response->assertForbidden();
    }

    public function test_staff_on_staff_route_is_allowed(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get('/test-staff-only');

        $response->assertOk();
    }

    public function test_member_on_member_route_is_allowed(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->get('/test-member-only');

        $response->assertOk();
    }

    public function test_staff_on_member_route_is_forbidden(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get('/test-member-only');

        $response->assertForbidden();
    }

    public function test_member_and_staff_both_allowed_when_multiple_roles_specified(): void
    {
        $member = User::factory()->create();
        $staff = User::factory()->staff()->create();

        $this->actingAs($member)->get('/test-member-or-staff')->assertOk();
        $this->actingAs($staff)->get('/test-member-or-staff')->assertOk();
    }
}
