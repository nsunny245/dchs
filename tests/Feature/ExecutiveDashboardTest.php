<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExecutiveDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_renders_interactive_filters_and_operational_sections(): void
    {
        Campus::create(['name' => 'Okara Campus', 'code' => 'OKR', 'city' => 'Okara', 'is_active' => true]);
        Campus::create(['name' => 'Main Campus', 'code' => 'MAIN', 'city' => 'Main', 'is_active' => true]);
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));

        $this->actingAs($user, 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertSee('Operations Dashboard')
            ->assertSee('Dashboard filters')
            ->assertSee('Quick Campus Login')
            ->assertSee('Okara Campus')
            ->assertDontSee('Main Campus')
            ->assertSee('Monthly activity')
            ->assertSee('Franchisor activity')
            ->assertSee('Student attendance')
            ->assertSee('Operational exceptions')
            ->assertSee('Quick actions');
    }

    public function test_campus_dashboard_hides_cross_campus_selector(): void
    {
        $campus = Campus::create(['name' => 'Campus Dashboard', 'code' => 'CD', 'city' => 'Okara', 'is_active' => true]);
        $user = User::factory()->create(['campus_id' => $campus->id]);
        $user->assignRole(Role::findOrCreate('Campus Principal', 'web'));

        $this->actingAs($user, 'campus')
            ->get('/campus')
            ->assertOk()
            ->assertSee('Campus Dashboard')
            ->assertDontSee('All campuses')
            ->assertSee('Reporting month');
    }
}
