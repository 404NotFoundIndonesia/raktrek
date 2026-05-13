<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_list_users_including_deactivated(): void
    {
        $staff   = User::factory()->staff()->create();
        $active  = User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();

        $response = $this->actingAs($staff)->get('/admin/users');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Admin/Users/Index')->has('users.data', 3)
        );
    }

    public function test_staff_can_deactivate_user(): void
    {
        $staff  = User::factory()->staff()->create();
        $member = User::factory()->create();

        $response = $this->actingAs($staff)->delete("/admin/users/{$member->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $member->id]);
    }

    public function test_staff_cannot_deactivate_themselves(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->delete("/admin/users/{$staff->id}");

        $response->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'deleted_at' => null]);
    }

    public function test_staff_can_activate_deactivated_user(): void
    {
        $staff   = User::factory()->staff()->create();
        $member  = User::factory()->create();
        $member->delete();

        $response = $this->actingAs($staff)->post("/admin/users/{$member->id}/activate");

        $response->assertRedirect();
        $this->assertNull($member->fresh()->deleted_at);
    }

    public function test_staff_can_change_user_role(): void
    {
        $staff  = User::factory()->staff()->create();
        $member = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($staff)->patch("/admin/users/{$member->id}/role", [
            'role' => 'staff',
        ]);

        $response->assertRedirect();
        $this->assertEquals('staff', $member->fresh()->role);
    }

    public function test_invalid_role_rejected(): void
    {
        $staff  = User::factory()->staff()->create();
        $member = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($staff)->patch("/admin/users/{$member->id}/role", [
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_member_cannot_manage_users(): void
    {
        $member  = User::factory()->create(['role' => 'member']);
        $another = User::factory()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->delete("/admin/users/{$another->id}")->assertForbidden();
    }
}
