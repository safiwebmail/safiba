<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New Customer',
            'email' => 'newcustomer@example.com',
            'phone' => '+93 700 999 999',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'role']]]);
    }

    public function test_user_can_login_and_get_me(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@safitailoring.com',
            'password' => 'password',
        ]);

        $login->assertOk();
        $token = $login->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'customer@safitailoring.com')
            ->assertJsonPath('data.role', 'customer');
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@safitailoring.com',
            'password' => 'wrongpassword',
        ])->assertStatus(422);
    }

    public function test_logout_revokes_token(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@safitailoring.com',
            'password' => 'password',
        ])->json('data.token');

        $user = User::where('email', 'customer@safitailoring.com')->first();
        $this->assertEquals(1, $user->tokens()->count());

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

        $this->assertEquals(0, $user->fresh()->tokens()->count());
        $this->assertNull(\Laravel\Sanctum\PersonalAccessToken::findToken($token));
    }

    public function test_change_password(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@safitailoring.com',
            'password' => 'password',
        ])->json('data.token');

        $this->withToken($token)->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@safitailoring.com',
            'password' => 'newpassword123',
        ])->assertOk();
    }
}