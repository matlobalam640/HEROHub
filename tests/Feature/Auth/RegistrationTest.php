<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_route_redirects_to_public_membership_site(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(config('heroportal.membership_subscribe_url'));
    }

    public function test_registration_post_redirects_to_public_membership_site(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(config('heroportal.membership_subscribe_url'));
        $this->assertGuest();
    }
}
