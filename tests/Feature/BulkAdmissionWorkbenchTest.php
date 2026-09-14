<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\User;
use App\Services\Admissions\BulkAdmissionService;
use App\Services\Fees\AdmissionVoucherReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkAdmissionWorkbenchTest extends TestCase
{
    use RefreshDatabase;

    private Campus $campus;

    private Course $course;

    private AcademicSession $session;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campus = Campus::create([
            'name' => 'Chichawatni Campus',
            'code' => 'CHI',
            'city' => 'Chichawatni',
            'address' => 'College Road',
            'phone' => '03000000000',
            'email' => 'chi@example.test',
            'is_active' => true,
        ]);
        $this->course = Course::create([
            'name' => 'Pharmacy Technician',
            'code' => 'PT',
            'duration_months' => 24,
            'is_active' => true,
        ]);
        $this->session = AcademicSession::create([
            'campus_id' => $this->campus->id,
            'name' => '2026-2028',
            'start_date' => '2026-09-01',
            'end_date' => '2028-08-31',
            'is_active' => true,
        ]);
        FeeStructure::create([
            'name' => 'PT 2026 Plan',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'total_fee' => 100000,
            'installment_count' => 5,
            'status' => 'active',
            'version' => 1,
        ]);
        $this->actor = User::factory()->create(['campus_id' => $this->campus->id]);
    }

    public function test_it_imports_an_enrolled_student_with_opening_balance_and_no_vouchers(): void
    {
        $batch = app(BulkAdmissionService::class)->import([$this->validRow()], $this->actor, $this->defaults());

        $this->assertSame(1, $batch->successful_rows);
        $this->assertSame(0, $batch->failed_rows);
        $student = Student::withoutGlobalScopes()->firstOrFail();
        $this->assertFalse($student->profile_complete);
        $this->assertFalse($student->user->status);
        $this->assertSame('enrolled', $student->admission->status);
        $this->assertStringStartsWith('DGC-CHI-PT-', $student->enrollment_number);
        $this->assertSame('bulk_import', $student->feeAccount->source);
        $this->assertEquals(100000, $student->feeAccount->original_fee);
        $this->assertEquals(15000, $student->feeAccount->concession_amount);
        $this->assertEquals(85000, $student->feeAccount->net_payable);
        $this->assertEquals(25000, $student->feeAccount->amount_paid);
        $this->assertEquals(60000, $student->feeAccount->balance);
        $this->assertDatabaseCount('fee_vouchers', 0);
        $this->assertDatabaseCount('student_installments', 0);
        $this->assertDatabaseHas('fee_payments', [
            'student_id' => $student->id,
            'fee_voucher_id' => null,
            'amount' => 25000,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('student_import_rows', ['status' => 'imported', 'student_id' => $student->id]);
    }

    public function test_a_duplicate_cnic_fails_only_that_row_without_duplicate_student_records(): void
    {
        $row = $this->validRow();
        $batch = app(BulkAdmissionService::class)->import([$row, $row], $this->actor, $this->defaults());

        $this->assertSame(1, $batch->successful_rows);
        $this->assertSame(1, $batch->failed_rows);
        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseCount('student_fee_accounts', 1);
        $this->assertDatabaseCount('student_import_rows', 2);
    }

    public function test_later_schedule_covers_only_the_outstanding_opening_balance(): void
    {
        app(BulkAdmissionService::class)->import([$this->validRow()], $this->actor, $this->defaults());
        $student = Student::withoutGlobalScopes()->firstOrFail();
        $admission = $student->admission;
        $admission->update([
            'custom_tuition_fee' => 100000,
            'custom_admission_fee' => 0,
            'custom_installment_count' => 5,
            'custom_installment_interval_months' => 1,
            'custom_installment_start_date' => '2026-09-10',
            'custom_installments' => [
                ['title' => 'Installment #1', 'amount' => 12000, 'due_date' => '2026-09-10'],
                ['title' => 'Installment #2', 'amount' => 12000, 'due_date' => '2026-10-10'],
                ['title' => 'Installment #3', 'amount' => 12000, 'due_date' => '2026-11-10'],
                ['title' => 'Installment #4', 'amount' => 12000, 'due_date' => '2026-12-10'],
                ['title' => 'Installment #5', 'amount' => 12000, 'due_date' => '2027-01-10'],
            ],
        ]);

        app(AdmissionVoucherReconciliationService::class)->reconcile($student->feeAccount, $this->actor->id);

        $vouchers = $student->feeAccount->vouchers()->orderBy('due_date')->get();
        $this->assertCount(5, $vouchers);
        $this->assertEquals(60000, $vouchers->sum('total_amount'));
        $this->assertEquals(12000, $vouchers[0]->total_amount);
        $this->assertEquals(0, $vouchers->sum('paid_amount'));
        $this->assertDatabaseCount('payment_allocations', 0);
        $this->assertDatabaseHas('fee_payments', ['amount' => 25000, 'fee_voucher_id' => null]);
        $this->assertEquals(60000, $student->feeAccount->fresh()->balance);
    }

    private function defaults(): array
    {
        return [
            'campus_id' => $this->campus->id,
            'academic_session_id' => $this->session->id,
            'course_id' => $this->course->id,
            'previous_education' => 'Matric with Biology',
            'shift' => 'morning',
            'admission_date' => '2026-09-09',
            'payment_date' => '2026-09-09',
            'payment_method' => 'cash',
        ];
    }

    private function validRow(): array
    {
        return [
            'applicant_name' => 'Ayesha Saleem',
            'father_name' => 'Muhammad Saleem',
            'dob' => '2007-03-12',
            'gender' => 'female',
            'phone' => '03001234567',
            'cnic' => '36502-1234567-8',
            'address' => 'Chichawatni, Punjab',
            'concession_amount' => 15000,
            'amount_paid' => 25000,
            'concession_reason' => 'Existing student concession',
        ];
    }
}
