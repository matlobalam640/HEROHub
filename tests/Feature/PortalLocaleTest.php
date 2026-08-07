<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_switch_portal_locale(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post(route('portal.locale.update'), [
            'locale' => 'fr',
        ]);

        $response->assertRedirect();
        $this->assertSame('fr', session('portal_locale'));
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post(route('portal.locale.update'), [
            'locale' => 'de',
        ]);

        $response->assertSessionHasErrors('locale');
        $this->assertNull(session('portal_locale'));
    }
}
