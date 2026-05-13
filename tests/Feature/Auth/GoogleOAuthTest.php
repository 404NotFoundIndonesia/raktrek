<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    private function mockSocialiteUser(string $id, string $name, string $email): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($id);
        $socialiteUser->shouldReceive('getName')->andReturn($name);
        $socialiteUser->shouldReceive('getEmail')->andReturn($email);

        $provider = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')->with('google')->andReturn($provider);

        $this->app->instance(SocialiteFactory::class, $socialite);
    }

    public function test_google_redirect_redirects_to_google(): void
    {
        $provider = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')->with('google')->andReturn($provider);

        $this->app->instance(SocialiteFactory::class, $socialite);

        $response = $this->get('/auth/google');

        $response->assertRedirect();
    }

    public function test_callback_creates_new_user_with_google_id(): void
    {
        $this->mockSocialiteUser('google-123', 'Google User', 'google@example.com');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email'     => 'google@example.com',
            'google_id' => 'google-123',
        ]);
    }

    public function test_callback_links_google_id_to_existing_user_without_duplicate(): void
    {
        $existing = User::factory()->create(['email' => 'existing@example.com', 'google_id' => null]);

        $this->mockSocialiteUser('google-456', 'Existing User', 'existing@example.com');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($existing->fresh());

        // No duplicate user created
        $this->assertDatabaseCount('users', 1);
        $this->assertEquals('google-456', $existing->fresh()->google_id);
    }

    public function test_callback_does_not_overwrite_existing_google_id(): void
    {
        $existing = User::factory()->create([
            'email'     => 'linked@example.com',
            'google_id' => 'already-linked-id',
        ]);

        $this->mockSocialiteUser('new-id', 'Linked User', 'linked@example.com');

        $this->get('/auth/google/callback');

        // google_id unchanged
        $this->assertEquals('already-linked-id', $existing->fresh()->google_id);
    }

    public function test_callback_rejects_deactivated_user(): void
    {
        $user = User::factory()->create(['email' => 'deleted@example.com']);
        $user->delete();

        $this->mockSocialiteUser('google-789', 'Deleted User', 'deleted@example.com');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('auth.login'));
        $this->assertGuest();
    }

    public function test_callback_authenticates_existing_user_with_google_id(): void
    {
        $existing = User::factory()->create([
            'email'     => 'already@example.com',
            'google_id' => 'gid-999',
        ]);

        $this->mockSocialiteUser('gid-999', 'Already Linked', 'already@example.com');

        $this->get('/auth/google/callback')->assertRedirect('/');

        $this->assertAuthenticatedAs($existing);
    }
}
