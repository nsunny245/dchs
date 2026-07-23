<?php

namespace Tests\Feature;

use App\Actions\FinalizeAdmissionAction;
use App\Models\AcademicSession;
use App\Models\Admission;
use App\Models\AdmissionDraft;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\FeeVoucher;
use App\Models\Student;
use App\Models\StudentFeeAccount;
use App\Models\StudentInstallment;
use App\Models\StudentLedgerEntry;
use App\Models\User;
use App\Services\Admissions\AdmissionDraftService;
use App\Services\Fees\ConcessionCalculator;
use App\Services\Fees\InstallmentPlanGenerator;
use App\Services\Fees\VoucherGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdmissionWorkflowFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_can_be_saved_and_resumed_without_a_complete_admission(): void
    {
        $campus = Campus::create([
            'name' => 'Okara Campus', 'city' => 'Okara', 'address' => 'Okara',
            'phone' => '03001234567', 'email' => 'campus@example.test', 'is_active' => true,
        ]);
        $user = User::factory()->create(['campus_id' => $campus->id]);

        $draft = app(AdmissionDraftService::class)->save(
            ['applicant_name' => 'Draft Student', 'campus_id' => $campus->id],
            $user,
            step: 2,
        );

        $this->assertDatabaseHas('admission_drafts', [
            'uuid' => $draft->uuid,
            'created_by' => $user->id,
            'current_step' => 2,
            'status' => 'draft',
        ]);
        $this->assertSame('Draft Student', AdmissionDraft::first()->payload['applicant_name']);

        $sameDraft = app(AdmissionDraftService::class)->save($draft->payload, $user, $draft->uuid, 2);
        $this->assertSame(1, $sameDraft->version, 'An unchanged autosave must not create a new draft revision.');
    }

    public function test_installments_use_exact_paisa_and_put_rounding_remainder_on_final_installment(): void
    {
        $schedule = app(InstallmentPlanGenerator::class)->generate('100.00', 3, now());

        $this->assertSame([3333, 3333, 3334], array_column($schedule, 'net_paisa'));
        $this->assertSame(10000, array_sum(array_column($schedule, 'net_paisa')));
    }

    public function test_installment_dates_are_deterministic(): void
    {
        $schedule = app(InstallmentPlanGenerator::class)->generate('12000', 3, now()->setDate(2026, 1, 31));

        $this->assertSame('2026-01-31', $schedule[0]['due_date']);
        $this->assertSame('2026-02-28', $schedule[1]['due_date']);
        $this->assertSame('2026-03-31', $schedule[2]['due_date']);
    }

    public function test_decimal_money_and_concessions_are_calculated_without_binary_float_drift(): void
    {
        $money = app(InstallmentPlanGenerator::class);
        $concessions = app(ConcessionCalculator::class);

        $this->assertSame(1001, $money->toPaisa('10.005'));
        $this->assertSame(250000, $concessions->calculate('10000.00', 'fixed', '2500.00'));
        $this->assertSame(125000, $concessions->calculate('10000.00', 'percentage', '12.50'));
    }

    public function test_voucher_preview_is_read_only_and_balances_to_the_plan(): void
    {
        $schedule = app(VoucherGenerationService::class)->previewPlan([
            'tuition' => '100.00',
            'one_time' => '25.00',
            'examination' => '10.00',
            'concession' => '15.00',
            'installment_count' => 3,
            'admission_date' => '2026-01-31',
        ]);

        $this->assertSame(4, count($schedule));
        $this->assertSame(12000, array_sum(array_column($schedule, 'net_paisa')));
        $this->assertDatabaseCount('fee_vouchers', 0);
    }

    public function test_finalization_is_idempotent_and_creates_financial_foundation_once(): void
    {
        $campus = Campus::create([
            'name' => 'Main Campus', 'city' => 'Okara', 'address' => 'Okara',
            'phone' => '03001234567', 'email' => 'main@example.test', 'is_active' => true,
        ]);
        $course = Course::create([
            'name' => 'Demo Program', 'code' => 'DEMO', 'duration_months' => 12,
            'eligibility' => 'Matric', 'description' => 'Demo', 'is_active' => true,
        ]);
        $session = AcademicSession::create(['name' => '2026-2027', 'is_active' => true]);
        FeeStructure::create(['course_id' => $course->id, 'total_fee' => 10000, 'installment_count' => 3]);
        $admission = Admission::create([
            'applicant_name' => 'Presentation Student', 'father_name' => 'Guardian',
            'father_phone' => '03001234567', 'cnic' => '35202-1234567-1',
            'dob' => '2005-01-01', 'gender' => 'female', 'phone' => '03007654321',
            'address' => 'Okara', 'campus_id' => $campus->id, 'course_id' => $course->id,
            'academic_session_id' => $session->id, 'admission_date' => '2026-07-23',
            'status' => 'submitted', 'concession_amount' => 0,
        ]);

        $action = app(FinalizeAdmissionAction::class);
        $first = $action->execute($admission);
        $second = $action->execute($admission->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Student::where('admission_id', $admission->id)->count());
        $this->assertSame(1, StudentFeeAccount::where('admission_id', $admission->id)->count());
        $this->assertSame(3, StudentInstallment::where('admission_id', $admission->id)->count());
        $this->assertSame(1, StudentLedgerEntry::where('student_id', $first->id)->count());
        $this->assertSame(
            (int) round((float) StudentFeeAccount::where('admission_id', $admission->id)->value('net_payable') * 100),
            StudentInstallment::where('admission_id', $admission->id)->sum('net_paisa'),
        );
        $this->assertSame(3, FeeVoucher::where('admission_id', $admission->id)->whereNotNull('installment_id')->count());
        $this->assertSame(3, FeeVoucher::where('admission_id', $admission->id)->distinct('voucher_number')->count('voucher_number'));
    }

    public function test_unapproved_concession_rolls_back_finalization(): void
    {
        $campus = Campus::create([
            'name' => 'Concession Campus', 'city' => 'Okara', 'address' => 'Okara',
            'phone' => '03001234567', 'email' => 'concession@example.test', 'is_active' => true,
        ]);
        $course = Course::create([
            'name' => 'Concession Program', 'code' => 'CONC', 'duration_months' => 12,
            'eligibility' => 'Matric', 'description' => 'Demo', 'is_active' => true,
        ]);
        $session = AcademicSession::create(['name' => '2027-2028', 'is_active' => true]);
        FeeStructure::create(['course_id' => $course->id, 'total_fee' => 10000, 'installment_count' => 2]);
        $admission = Admission::create([
            'applicant_name' => 'Pending Concession', 'father_name' => 'Guardian',
            'father_phone' => '03001234567', 'cnic' => '35202-7654321-1',
            'dob' => '2005-01-01', 'gender' => 'female', 'phone' => '03007654321',
            'address' => 'Okara', 'campus_id' => $campus->id, 'course_id' => $course->id,
            'academic_session_id' => $session->id, 'admission_date' => '2026-07-23',
            'status' => 'submitted', 'concession_amount' => 1000, 'concession_status' => 'pending',
        ]);

        try {
            app(FinalizeAdmissionAction::class)->execute($admission);
            $this->fail('Finalization should reject an unapproved concession.');
        } catch (ValidationException) {
            $this->assertDatabaseMissing('students', ['admission_id' => $admission->id]);
            $this->assertNull($admission->fresh()->finalized_at);
            $this->assertSame(0, FeeVoucher::where('admission_id', $admission->id)->count());
        }
    }
}
