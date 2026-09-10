<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIKANDI');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::where('email', 'superadmin@ciamis.go.id')->first();

        $response = $this->post('/login', [
            'login' => 'superadmin@ciamis.go.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $response = $this->from('/login')->post('/login', [
            'login' => 'superadmin@ciamis.go.id',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::where('email', 'superadmin@ciamis.go.id')->first();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
