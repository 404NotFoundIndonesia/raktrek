<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_with_user_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertInertia(fn ($p) => $p
            ->component('Profile/Index')
            ->where('user.id', $user->id)
            ->where('user.email', $user->email)
        );
    }

    public function test_guest_redirected_from_profile(): void
    {
        $this->get('/profile')->assertRedirect(route('auth.login'));
    }

    public function test_update_profile_saves_changes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name'    => 'Updated Name',
            'email'   => $user->email,
            'phone'   => '089999999',
            'address' => 'New Address 123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'      => $user->id,
            'name'    => 'Updated Name',
            'phone'   => '089999999',
            'address' => 'New Address 123',
        ]);
    }

    public function test_update_profile_requires_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', ['name' => '', 'email' => $user->email])
            ->assertSessionHasErrors('name');
    }

    public function test_update_profile_rejects_duplicate_email_from_another_user(): void
    {
        $other = User::factory()->create(['email' => 'taken@example.com']);
        $user  = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'name'  => $user->name,
                'email' => 'taken@example.com',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_update_profile_allows_same_email_for_own_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'name'  => $user->name,
                'email' => $user->email,
            ])
            ->assertSessionDoesntHaveErrors('email');
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->put('/profile', ['name' => 'Hacker'])
            ->assertRedirect(route('auth.login'));
    }
}
