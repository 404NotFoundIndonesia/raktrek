<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_logout_redirects_gracefully(): void
    {
        $response = $this->post('/logout');

        // CSRF token is required; session is already guest, redirect is acceptable
        $response->assertRedirect('/');
    }

    public function test_after_logout_member_route_redirects_to_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->get('/profile')->assertRedirect(route('auth.login'));
    }
}
