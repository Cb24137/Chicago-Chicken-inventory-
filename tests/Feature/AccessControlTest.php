<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_every_module(): void
    {
        foreach (['/dashboard', '/inventory', '/suppliers', '/purchases', '/storage-locations', '/waste-expiry', '/admin/staff'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_users_can_log_in_and_log_out(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'email' => 'staff@test.com']);

        $this->post('/login', ['email' => 'staff@test.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($staff);

        $this->post(route('logout'))->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_staff_see_the_staff_dashboard_without_admin_menu(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('staff-dashboard')
            ->assertSee('Staff Dashboard')
            ->assertDontSee('Staff Management');
    }

    public function test_admin_sees_the_admin_dashboard_with_staff_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('admin-dashboard')
            ->assertSee('Executive Dashboard')
            ->assertSee('Management Overview')
            ->assertSee('Staff Management')
            ->assertSee(route('admin.staff.index'), false);
    }

    public function test_staff_cannot_use_admin_staff_management(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('admin.staff.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.staff.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('admin.staff.store'), [
            'name' => 'Sneaky',
            'email' => 'sneaky@test.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@test.com']);
    }

    public function test_admin_can_manage_staff_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.staff.index'))->assertOk()->assertSee('Manage Staff');

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'New Staff',
            'email' => 'new@test.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])->assertRedirect(route('admin.staff.index'));

        $this->assertDatabaseHas('users', ['email' => 'new@test.com', 'role' => 'staff']);
    }

    public function test_staff_can_open_every_inventory_module_page(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        foreach ([
            'inventory.index', 'inventory.create',
            'suppliers.index', 'suppliers.create',
            'purchases.index', 'purchases.create',
            'storage-locations.index', 'storage-locations.create',
            'waste-expiry.index', 'waste-expiry.create',
        ] as $routeName) {
            $this->actingAs($staff)->get(route($routeName))->assertOk();
        }
    }
}
