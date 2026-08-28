<?php

namespace Tests\Feature;

use App\Filament\Widgets\CampusQuickAccessWidget;
use App\Models\Campus;
use App\Models\CampusDashboardAccessLog;
use App\Models\Staff;
use App\Models\User;
use App\Services\Campus\CampusDashboardAccessService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CampusDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Super Admin', 'web');
        Role::findOrCreate('Campus Principal', 'web');
    }

    public function test_super_admin_sees_active_campuses_and_setup_state(): void
    {
        $superAdmin = $this->superAdmin();
        $availableCampus = $this->campus('Okara Campus', 'OKR');
        $unavailableCampus = $this->campus('Depalpur Campus', 'DPL');
        $inactiveCampus = $this->campus('Closed Campus', 'CLS', false);
        $this->principal($availableCampus);

        $this->actingAs($superAdmin, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CampusQuickAccessWidget::class)
            ->assertOk()
            ->assertSee($availableCampus->name)
            ->assertSee('Available')
            ->assertSee($unavailableCampus->name)
            ->assertSee('Setup required')
            ->assertDontSee($inactiveCampus->name);
    }

    public function test_super_admin_can_enter_a_campus_and_preserve_admin_session(): void
    {
        $superAdmin = $this->superAdmin();
        $campus = $this->campus('Okara Campus', 'OKR');
        $principal = $this->principal($campus);

        $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.campus-access.enter', $campus))
            ->assertRedirect(route('filament.campus.pages.dashboard'))
            ->assertSessionHas(CampusDashboardAccessService::SESSION_KEY);

        $this->assertAuthenticatedAs($superAdmin, 'admin');
        $this->assertAuthenticatedAs($principal, 'campus');
        $this->assertDatabaseHas('campus_dashboard_access_logs', [
            'super_admin_user_id' => $superAdmin->id,
            'campus_id' => $campus->id,
            'campus_user_id' => $principal->id,
            'exited_at' => null,
        ]);
    }

    public function test_campus_session_scopes_models_to_the_selected_campus(): void
    {
        $superAdmin = $this->superAdmin();
        $selectedCampus = $this->campus('Okara Campus', 'OKR');
        $otherCampus = $this->campus('Depalpur Campus', 'DPL');
        $this->principal($selectedCampus);
        $this->staff($selectedCampus, 'Selected Campus Staff');
        $this->staff($otherCampus, 'Other Campus Staff');

        $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.campus-access.enter', $selectedCampus))
            ->assertRedirect();

        Filament::setCurrentPanel(Filament::getPanel('campus'));

        $this->assertSame(['Selected Campus Staff'], Staff::query()->pluck('full_name')->all());
    }

    public function test_return_closes_campus_access_without_logging_out_super_admin(): void
    {
        $superAdmin = $this->superAdmin();
        $campus = $this->campus('Okara Campus', 'OKR');
        $principal = $this->principal($campus);

        $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.campus-access.enter', $campus));

        $this->actingAs($principal, 'campus')
            ->post(route('campus-access.exit'))
            ->assertRedirect(route('filament.admin.pages.dashboard'))
            ->assertSessionMissing(CampusDashboardAccessService::SESSION_KEY);

        $this->assertAuthenticatedAs($superAdmin, 'admin');
        $this->assertGuest('campus');
        $this->assertNotNull(CampusDashboardAccessLog::query()->firstOrFail()->exited_at);
    }

    public function test_non_super_admin_cannot_start_campus_access(): void
    {
        $campus = $this->campus('Okara Campus', 'OKR');
        $principal = $this->principal($campus);

        $this->actingAs($principal, 'admin')
            ->post(route('admin.campus-access.enter', $campus))
            ->assertForbidden();

        $this->assertGuest('campus');
        $this->assertDatabaseCount('campus_dashboard_access_logs', 0);
    }

    public function test_inactive_campus_cannot_be_opened(): void
    {
        $superAdmin = $this->superAdmin();
        $campus = $this->campus('Closed Campus', 'CLS', false);
        $this->principal($campus);

        $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.campus-access.enter', $campus))
            ->assertForbidden();

        $this->assertGuest('campus');
    }

    public function test_campus_without_active_principal_returns_to_dashboard_with_guidance(): void
    {
        $superAdmin = $this->superAdmin();
        $campus = $this->campus('Depalpur Campus', 'DPL');

        $this->actingAs($superAdmin, 'admin')
            ->from(route('filament.admin.pages.dashboard'))
            ->post(route('admin.campus-access.enter', $campus))
            ->assertRedirect(route('filament.admin.pages.dashboard'))
            ->assertSessionHas('campus_access_error');

        $this->assertGuest('campus');
        $this->assertDatabaseCount('campus_dashboard_access_logs', 0);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['campus_id' => null, 'status' => true]);
        $user->assignRole('Super Admin');

        return $user;
    }

    private function principal(Campus $campus): User
    {
        $user = User::factory()->create([
            'campus_id' => $campus->id,
            'status' => true,
        ]);
        $user->assignRole('Campus Principal');

        return $user;
    }

    private function campus(string $name, string $code, bool $active = true): Campus
    {
        return Campus::create([
            'name' => $name,
            'code' => $code,
            'city' => str($name)->before(' Campus')->toString(),
            'address' => 'Test campus address',
            'phone' => '03001234567',
            'is_active' => $active,
        ]);
    }

    private function staff(Campus $campus, string $name): Staff
    {
        return Staff::create([
            'campus_id' => $campus->id,
            'full_name' => $name,
            'designation' => 'Teacher',
            'hire_date' => now()->toDateString(),
            'status' => 'active',
        ]);
    }
}
