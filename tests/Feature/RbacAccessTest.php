<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->createAdminUser();
    }

    private function createAdminUser(): void
    {
        $superAdminRole = Role::query()->where('slug', 'super-admin')->firstOrFail();
        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'username' => 'admin',
            'email' => 'admin@erp.local',
            'password' => 'admin123',
        ]);
        $admin->roles()->sync([$superAdminRole->id]);
    }

    public function test_guest_is_redirected_to_login_for_erp_routes(): void
    {
        $this->get(route('erp.accounts.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_login_and_access_admin_area(): void
    {
        $response = $this->post(route('login.store'), [
            'login' => 'admin@erp.local',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('erp.accounts.dashboard'));

        $this->get(route('erp.admin.roles'))
            ->assertOk()
            ->assertSee('Roles Matrix');
    }

    public function test_admin_can_login_with_username(): void
    {
        $response = $this->post(route('login.store'), [
            'login' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('erp.accounts.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_viewer_is_forbidden_from_admin_routes(): void
    {
        $viewerRole = Role::query()->where('slug', 'viewer')->firstOrFail();
        $viewer = User::factory()->create([
            'email' => 'viewer@erp.local',
            'password' => 'viewer123',
        ]);
        $viewer->roles()->sync([$viewerRole->id]);

        $this->actingAs($viewer)
            ->get(route('erp.admin.dashboard'))
            ->assertForbidden();
    }

    public function test_viewer_does_not_see_admin_menu_or_print_actions(): void
    {
        $viewerRole = Role::query()->where('slug', 'viewer')->firstOrFail();
        $viewer = User::factory()->create([
            'email' => 'viewer2@erp.local',
            'password' => 'viewer123',
        ]);
        $viewer->roles()->sync([$viewerRole->id]);

        $this->actingAs($viewer)
            ->get(route('erp.accounts.dashboard'))
            ->assertOk()
            ->assertDontSee('Access management');

        $this->actingAs($viewer)
            ->get(route('erp.accounts.vouchers.jv'))
            ->assertOk()
            ->assertDontSee('Voucher print');
    }
}

