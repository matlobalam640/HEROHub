<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortalGlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('customer', 'web');
    }

    public function test_admin_can_search_customers_by_email(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->create([
            'name' => 'Acoduret Test',
            'email' => 'acoduret@gmail.com',
        ])->assignRole('customer');

        $this->actingAs($admin)
            ->getJson(route('portal.search', ['q' => 'acoduret@gmail.com']))
            ->assertOk()
            ->assertJsonFragment(['label' => 'Acoduret Test'])
            ->assertJsonFragment(['meta' => 'acoduret@gmail.com']);
    }

    public function test_customer_cannot_use_live_portal_search(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer)
            ->getJson(route('portal.search', ['q' => 'anything']))
            ->assertForbidden();
    }
}
