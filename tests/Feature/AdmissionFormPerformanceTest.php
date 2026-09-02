<?php

namespace Tests\Feature;

use App\Filament\Resources\AdmissionResource\Pages\CreateAdmission;
use App\Models\AcademicSession;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeHead;
use App\Models\FeeStructure;
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
            ->assertSee('Submit Admission & Generate Documents');

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
}
