<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_current_password_updates_to_new_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password'      => 'old-password',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_wrong_current_password_returns_validation_error(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password'      => 'wrong',
                'password'              => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_new_password_confirmation_must_match(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password'      => 'old-password',
                'password'              => 'new-password',
                'password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_new_password_min_8_chars(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password'      => 'old-password',
                'password'              => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_old_password_no_longer_works_after_change(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)->put('/profile/password', [
            'current_password'      => 'old-password',
            'password'              => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $this->assertFalse(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_guest_cannot_change_password(): void
    {
        $this->put('/profile/password', [
            'current_password'      => 'anything',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('auth.login'));
    }
}
