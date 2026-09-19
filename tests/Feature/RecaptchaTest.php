<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.recaptcha.site_key', 'test-site-key');
        Config::set('services.recaptcha.secret_key', 'test-secret-key');
        Config::set('services.recaptcha.min_score', 0.5);
    }

    public function test_login_requires_recaptcha_response_when_enabled(): void
    {
        $response = $this->from('/login')->post('/login', [
            'login' => 'testuser',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['g-recaptcha-response']);
    }

    public function test_login_fails_when_recaptcha_verification_fails(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'testuser',
            'password' => 'secret123',
            'g-recaptcha-response' => 'fake-token',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login' => 'Verifikasi reCAPTCHA gagal, silakan coba lagi.']);
    }

    public function test_login_fails_when_recaptcha_score_is_below_threshold(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.2,
                'action' => 'login',
            ], 200),
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'testuser',
            'password' => 'secret123',
            'g-recaptcha-response' => 'fake-token',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login' => 'Verifikasi reCAPTCHA gagal, silakan coba lagi.']);
    }

    public function test_login_passes_recaptcha_with_valid_score_and_checks_credentials(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
                'action' => 'login',
            ], 200),
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'nonexistent@ciamis.go.id',
            'password' => 'password123',
            'g-recaptcha-response' => 'valid-token',
        ]);

        $response->assertRedirect('/login');
        // reCAPTCHA passed, so error is about invalid credentials, NOT reCAPTCHA!
        $response->assertSessionHasErrors(['login' => 'Kombinasi email/username dan password tidak cocok atau akun Anda tidak aktif.']);
    }

    public function test_login_passes_recaptcha_v2_checkbox_where_score_is_null(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'nonexistent@ciamis.go.id',
            'password' => 'password123',
            'g-recaptcha-response' => 'valid-v2-token',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login' => 'Kombinasi email/username dan password tidak cocok atau akun Anda tidak aktif.']);
    }

    public function test_login_handles_recaptcha_network_error_gracefully(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => function () {
                throw new ConnectionException('Connection timed out');
            },
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'testuser',
            'password' => 'secret123',
            'g-recaptcha-response' => 'fake-token',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login' => 'Gagal menghubungi server verifikasi reCAPTCHA, silakan coba lagi.']);
    }
}
