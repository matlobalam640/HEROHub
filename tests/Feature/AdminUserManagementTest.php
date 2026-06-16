<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_another_user_with_password(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $this->actingAs($admin)
            ->from(route('portal.coming-soon', ['page' => 'settings']))
            ->delete(route('admin.users.destroy', $other), [
                'password' => 'password',
            ])
            ->assertRedirect(route('portal.coming-soon', ['page' => 'settings']))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin), [
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_non_admin_cannot_delete_users(): void
    {
        $dispatch = User::factory()->create();
        $dispatch->assignRole('dispatch');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $this->actingAs($dispatch)
            ->delete(route('admin.users.destroy', $other), [
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $other->id]);
    }

    public function test_admin_can_delete_another_admin_when_multiple_admins_exist(): void
    {
        $first = User::factory()->create();
        $first->assignRole('admin');
        $second = User::factory()->create();
        $second->assignRole('admin');

        $this->actingAs($first)
            ->delete(route('admin.users.destroy', $second), [
                'password' => 'password',
            ])
            ->assertRedirect(route('portal.coming-soon', ['page' => 'settings']));

        $this->assertDatabaseMissing('users', ['id' => $second->id]);
    }
}
