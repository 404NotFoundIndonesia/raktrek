<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\HasApiTokens;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_uses_soft_deletes(): void
    {
        $this->assertContains(SoftDeletes::class, class_uses_recursive(User::class));
    }

    public function test_user_has_api_tokens(): void
    {
        $this->assertContains(HasApiTokens::class, class_uses_recursive(User::class));
    }

    public function test_default_role_is_member(): void
    {
        $user = new User();

        $this->assertEquals('member', $user->role);
    }

    public function test_password_is_hidden_from_serialization(): void
    {
        $user = User::factory()->make();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('google_id', $array);
    }

    public function test_factory_creates_member_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('member', $user->role);
    }

    public function test_factory_staff_state_creates_staff(): void
    {
        $user = User::factory()->staff()->create();

        $this->assertEquals('staff', $user->role);
    }

    public function test_soft_deleted_user_excluded_from_default_query(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    public function test_address_is_fillable(): void
    {
        $this->assertContains('address', (new User())->getFillable());
    }
}
