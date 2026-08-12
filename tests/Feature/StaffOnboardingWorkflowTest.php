<?php

namespace Tests\Feature;

use App\Filament\Resources\StaffResource\Pages\CreateStaffWizard;
use App\Filament\Resources\StaffResource\Pages\EditStaff;
use App\Filament\Resources\StaffResource\RelationManagers\DocumentsRelationManager;
use App\Models\Campus;
use App\Models\Staff;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffOnboardingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Role::findOrCreate('Super Admin', 'web');
    }

    public function test_onboarding_page_renders_requested_lists_without_cnic_dates(): void
    {
        [$campus, $user] = $this->signInAsSuperAdmin();

        $queries = [];
        DB::listen(static function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        Livewire::test(CreateStaffWizard::class)
            ->assertOk()
            ->assertFormFieldDoesNotExist('cnic_issue_date')
            ->assertFormFieldDoesNotExist('cnic_expiry_date')
            ->assertFormFieldExists('degree_title')
            ->assertFormFieldExists('specialization')
            ->assertFormFieldExists('institution')
            ->assertFormFieldExists('passing_year')
            ->assertFormFieldExists('teaching_experience_years')
            ->assertFormFieldExists('clinical_experience_years')
            ->assertFormFieldExists('document_cnic_front')
            ->assertFormFieldExists('document_cnic_back')
            ->assertFormFieldExists('document_education')
            ->assertSee('Missing (optional)');

        $lookupQueries = collect($queries)->filter(fn (string $sql): bool => str_contains($sql, 'from "campuses"')
            || str_contains($sql, 'from "courses"')
            || str_contains($sql, 'from "users"')
            || str_contains($sql, 'from `campuses`')
            || str_contains($sql, 'from `courses`')
            || str_contains($sql, 'from `users`')
        );

        $this->assertLessThanOrEqual(4, $lookupQueries->count());
        $this->assertSame($campus->id, $user->campus_id);
    }

    public function test_teacher_can_be_onboarded_without_documents(): void
    {
        [$campus] = $this->signInAsSuperAdmin();

        Livewire::test(CreateStaffWizard::class)
            ->fillForm([
                'campus_id' => $campus->id,
                'employee_id' => 'DGC-TST-TEA-0001',
                'full_name' => 'Test Teacher',
                'father_or_spouse_name' => 'Test Parent',
                'cnic' => '35202-1234567-1',
                'highest_qualification' => 'Master',
                'degree_title' => 'M.Sc.',
                'specialization' => 'Education',
                'institution' => 'University of the Punjab',
                'passing_year' => (int) date('Y') - 2,
                'teaching_experience_years' => 3,
                'clinical_experience_years' => 0,
                'staff_category' => 'teaching',
                'designation' => 'Lecturer',
                'employment_type' => 'full_time',
                'appointment_status' => 'probation',
                'joining_date' => now()->toDateString(),
                'gross_salary' => 75000,
            ])
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('staff', [
            'campus_id' => $campus->id,
            'full_name' => 'Test Teacher',
            'record_status' => 'active',
        ]);
        $this->assertDatabaseHas('teacher_academics', [
            'degree_title' => 'M.Sc.',
            'specialization' => 'Education',
        ]);
        $this->assertDatabaseHas('employment_records', [
            'designation' => 'Lecturer',
            'appointment_status' => 'probation',
        ]);
        $this->assertDatabaseCount('staff_documents', 0);
        $this->assertSame('Test Teacher', Staff::query()->firstOrFail()->full_name);
    }

    public function test_admin_can_open_document_management_for_an_existing_teacher(): void
    {
        [$campus] = $this->signInAsSuperAdmin();
        $staff = Staff::create([
            'campus_id' => $campus->id,
            'employee_id' => 'DGC-TST-TEA-0042',
            'full_name' => 'Existing Teacher',
            'designation' => 'Lecturer',
            'hire_date' => now(),
            'joining_date' => now(),
            'record_status' => 'active',
            'is_active' => true,
        ]);

        Livewire::test(EditStaff::class, ['record' => $staff->getRouteKey()])
            ->assertOk()
            ->assertFormFieldDoesNotExist('cnic_issue_date')
            ->assertFormFieldDoesNotExist('cnic_expiry_date')
            ->assertSee('Staff Documents');

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $staff,
            'pageClass' => EditStaff::class,
        ])
            ->assertOk()
            ->assertSee('Upload Missing Document');
    }

    private function signInAsSuperAdmin(): array
    {
        $campus = Campus::create([
            'name' => 'Test Campus',
            'code' => 'TST',
            'city' => 'Okara',
            'address' => 'Test Address',
            'phone' => '03001234567',
            'is_active' => true,
        ]);
        $user = User::factory()->create(['campus_id' => $campus->id]);
        $user->assignRole('Super Admin');

        $this->actingAs($user, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return [$campus, $user];
    }
}
