<?php

namespace Tests\Feature;

use App\Filament\Resources\AdmissionResource\Pages\CreateAdmission;
use App\Filament\Resources\AdmissionResource;
use App\Models\Admission;
use App\Models\AcademicSession;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdmissionFormPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_fee_plan_preview_requires_an_authenticated_panel_user(): void
    {
        $this->getJson(route('admin.admissions.fee-plan-preview', ['course_id' => 1]))
            ->assertUnauthorized();
    }

    public function test_admission_form_reuses_lookups_and_populates_fee_plan(): void
    {
        $campus = Campus::create([
            'name' => 'Performance Campus',
            'code' => 'PERF',
            'city' => 'Okara',
            'address' => 'Okara',
            'phone' => '03001234567',
            'email' => 'performance@example.test',
            'is_active' => true,
        ]);
        Campus::create([
            'name' => 'Main Campus',
            'code' => 'MAIN-PERF',
            'city' => 'Okara',
            'is_active' => true,
        ]);
        $course = Course::create([
            'name' => 'Performance Course',
            'code' => 'PERF-COURSE',
            'duration_months' => 12,
            'eligibility' => 'Matric',
            'description' => 'Performance test course',
            'is_active' => true,
        ]);
        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'is_active' => true,
        ]);
        FeeStructure::create([
            'course_id' => $course->id,
            'campus_id' => $campus->id,
            'academic_session_id' => $session->id,
            'total_fee' => 120000,
            'installment_count' => 12,
            'status' => 'active',
            'effective_date' => '2026-01-01',
        ]);
        FeeHead::create([
            'course_id' => $course->id,
            'name' => 'Admission Fee',
            'code' => 'ADMISSION_PERF',
            'category' => 'admission',
            'default_amount' => 10000,
            'applies_to' => 'new_enrollment',
        ]);

        $user = User::factory()->create(['campus_id' => $campus->id]);
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $queries = [];
        DB::listen(static function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $component = Livewire::test(CreateAdmission::class);

        $component
            ->assertSee('Save Draft')
            ->assertSee('Submit Admission & Generate Documents')
            ->assertDontSee('Main Campus');

        $lookupQueries = collect($queries)->filter(
            fn (string $sql): bool => str_contains($sql, 'from "courses"')
                || str_contains($sql, 'from "campuses"')
                || str_contains($sql, 'from "academic_sessions"'),
        );

        $this->assertLessThanOrEqual(3, $lookupQueries->count());
        $this->assertLessThanOrEqual(8, count($queries));

        $component
            ->set('data.campus_id', $campus->id)
            ->set('data.academic_session_id', $session->id)
            ->set('data.admission_date', '2026-08-12')
            ->set('data.custom_installment_start_date', '2026-08-12')
            ->set('data.course_id', $course->id)
            ->assertSet('data.custom_tuition_fee', 120000)
            ->assertSet('data.custom_admission_fee', '0.00')
            ->assertSet('data.custom_installment_count', 12);

        $installments = $component->get('data.custom_installments');
        $this->assertCount(12, $installments);
        $this->assertSame('10000.00', $installments[0]['amount']);
        $this->assertSame('2026-08-12', $installments[0]['due_date']);
        $this->assertSame('2027-07-12', $installments[11]['due_date']);

        $component->set('data.custom_installment_count', 4);
        $installments = $component->get('data.custom_installments');
        $this->assertCount(4, $installments);
        $this->assertSame('30000.00', $installments[0]['amount']);
        $this->assertSame('2026-11-12', $installments[3]['due_date']);

        $component->set('data.custom_installment_interval_months', 3);
        $installments = $component->get('data.custom_installments');
        $this->assertSame('2027-05-12', $installments[3]['due_date']);

        $this->getJson(route('admin.admissions.fee-plan-preview', [
            'course_id' => $course->id,
            'campus_id' => $campus->id,
            'academic_session_id' => $session->id,
            'admission_date' => '2026-08-12',
        ]))
            ->assertOk()
            ->assertJsonPath('custom_tuition_fee', 120000)
            ->assertJsonPath('custom_admission_fee', '0.00')
            ->assertJsonPath('custom_other_misc', '0.00')
            ->assertJsonPath('custom_installment_count', 12);

        $component
            ->call('create')
            ->assertNotified('Admission could not be submitted')
            ->assertHasErrors([
                'data.father_name',
            ]);

        $this->assertDatabaseCount('admissions', 0);
    }

    public function test_panels_use_fast_navigation_and_admission_rows_preload_action_relations(): void
    {
        $campus = Campus::create([
            'name' => 'Navigation Campus',
            'code' => 'NAV',
            'city' => 'Okara',
            'is_active' => true,
        ]);
        $course = Course::create([
            'name' => 'Navigation Course',
            'code' => 'NAV-COURSE',
            'duration_months' => 12,
            'is_active' => true,
        ]);
        $admission = Admission::create([
            'applicant_name' => 'Navigation Test Student',
            'father_name' => 'Navigation Test Guardian',
            'cnic' => '35202-1000009-9',
            'dob' => '2005-01-01',
            'gender' => 'male',
            'phone' => '03001000009',
            'address' => 'Okara',
            'campus_id' => $campus->id,
            'course_id' => $course->id,
        ]);

        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->assertTrue(Filament::getPanel('admin')->hasSpaMode());
        $this->assertTrue(Filament::getPanel('campus')->hasSpaMode());
        $this->assertTrue(Filament::getPanel('franchisor')->hasSpaMode());

        $record = AdmissionResource::getEloquentQuery()->findOrFail($admission->id);

        $this->assertTrue($record->relationLoaded('campus'));
        $this->assertTrue($record->relationLoaded('course'));
        $this->assertTrue($record->relationLoaded('student'));
    }

    public function test_new_admission_form_starts_without_a_fake_student_name(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateAdmission::class)
            ->assertSet('data.applicant_name', null);
    }

    public function test_complete_admission_form_submits_and_enrolls_the_student(): void
    {
        $campus = Campus::create([
            'name' => 'Submission Campus',
            'code' => 'SUB',
            'city' => 'Okara',
            'is_active' => true,
        ]);
        $course = Course::create([
            'name' => 'Submission Course',
            'code' => 'SUB-COURSE',
            'duration_months' => 12,
            'is_active' => true,
        ]);
        $session = AcademicSession::create([
            'name' => '2026-2027 Submission',
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'is_active' => true,
        ]);
        FeeStructure::create([
            'course_id' => $course->id,
            'campus_id' => $campus->id,
            'academic_session_id' => $session->id,
            'total_fee' => 120000,
            'installment_count' => 4,
            'status' => 'active',
            'effective_date' => '2026-01-01',
        ]);

        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateAdmission::class)
            ->set('data.applicant_name', 'Local Workflow Test Student')
            ->set('data.father_name', 'Local Workflow Test Guardian')
            ->set('data.cnic', '35202-1000010-0')
            ->set('data.dob', '2005-01-01')
            ->set('data.gender', 'male')
            ->set('data.phone', '03001000010')
            ->set('data.address', 'Local test address, Okara')
            ->set('data.father_phone', '03002000010')
            ->set('data.father_address', 'Local test address, Okara')
            ->set('data.campus_id', $campus->id)
            ->set('data.academic_session_id', $session->id)
            ->set('data.admission_date', '2026-11-06')
            ->set('data.custom_installment_start_date', '2026-11-06')
            ->set('data.course_id', $course->id)
            ->call('create')
            ->assertHasNoErrors();

        $admission = Admission::where('cnic', '35202-1000010-0')->firstOrFail();

        $this->assertNotNull($admission->finalized_at);
        $this->assertDatabaseHas('students', [
            'admission_id' => $admission->id,
            'full_name' => 'Local Workflow Test Student',
        ]);
        $this->assertSame(1, Student::where('admission_id', $admission->id)->count());
    }
}
