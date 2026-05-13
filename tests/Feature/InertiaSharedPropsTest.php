<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InertiaSharedPropsTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_props_present_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('appName')
            ->has('currentRouteName')
            ->has('user')
        );
    }

    public function test_user_prop_is_null_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('user', null)
        );
    }

    public function test_user_prop_contains_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('user.id', $user->id)
            ->where('user.email', $user->email)
        );
    }

    public function test_current_route_name_is_correct(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('currentRouteName', 'home')
        );
    }
}
