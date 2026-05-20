<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsScreenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $superAdminRole = \App\Models\Role::query()->where('slug', 'super-admin')->firstOrFail();
        $admin = \App\Models\User::factory()->create([
            'name' => 'Test Admin',
            'username' => 'test-admin',
            'email' => 'admin@erp.local',
            'password' => 'admin123',
        ]);
        $admin->roles()->sync([$superAdminRole->id]);
    }

    public function test_reports_accounts_screen_renders(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('erp.reports.screen', ['screen' => 'accounts']))
            ->assertOk()
            ->assertSee('Account Statement', false);
    }
}
