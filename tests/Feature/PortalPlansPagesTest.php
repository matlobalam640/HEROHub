<?php

namespace Tests\Feature;

use App\Http\Controllers\PlanController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalPlansPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_plan_routes_resolve_plan_controller(): void
    {
        $routes = [
            'portal.plans.retail' => 'retail',
            'portal.plans.small-business' => 'smallBusiness',
            'portal.plans.corporate' => 'corporate',
        ];

        foreach ($routes as $name => $method) {
            $route = route($name, [], false);
            $matched = app('router')->getRoutes()->match(
                \Illuminate\Http\Request::create($route, 'GET')
            );

            $this->assertSame(PlanController::class, $matched->getControllerClass());
            $this->assertSame($method, $matched->getActionMethod());
        }
    }

    public function test_guest_is_redirected_from_portal_plan_pages(): void
    {
        $this->get(route('portal.plans.retail'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_portal_plan_catalog_pages(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('portal.plans.retail'))
            ->assertOk()
            ->assertSee('Retail Membership Plans');

        $this->actingAs($admin)
            ->get(route('portal.plans.small-business'))
            ->assertOk()
            ->assertSee('Small Business Plans');

        $this->actingAs($admin)
            ->get(route('portal.plans.corporate'))
            ->assertOk()
            ->assertSee('Corporate Plans');
    }
}
