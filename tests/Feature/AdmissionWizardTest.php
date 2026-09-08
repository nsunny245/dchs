<?php

namespace Tests\Feature;

use App\Filament\Resources\AdmissionResource;
use App\Filament\Resources\AdmissionResource\Pages\EditAdmission;
use App\Models\AcademicSession;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeHead;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\FeeVoucher;
use App\Models\PaymentAllocation;
use App\Models\StudentFeeAccount;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\Fees\AdmissionFeeAgreementData;
use App\Services\Fees\AdmissionVoucherReconciliationService;
use App\Services\Fees\FeeVoucherCalculator;
use App\Services\Fees\FeeVoucherPdfService;
use App\Services\Fees\TuitionVoucherDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdmissionWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard campus, course, session, user
        $this->campus = Campus::create([
            'name' => 'Okara Main Campus',
            'city' => 'Okara',
            'address' => 'Okara bypass',
            'phone' => '03001234567',
            'email' => 'okara@dchs.edu.pk',
            'is_active' => true,
        ]);

        $this->course = Course::create([
            'name' => 'Allied Health CNA',
            'code' => 'CNA',
            'duration_months' => 24,
            'eligibility' => 'Matric',
            'description' => 'Certified Nursing Assistant',
            'is_active' => true,
        ]);

        $this->session = AcademicSession::create([
            'name' => '2026-2028',
            'is_active' => true,
        ]);

        // Seed CNA fee heads
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Admission Fee',
            'code' => 'ADMISSION_CNA',
            'category' => 'admission',
            'default_amount' => 10000.00,
            'applies_to' => 'new_enrollment',
        ]);
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Enrollment Fee',
            'code' => 'ENDOWMENT_CNA',
            'category' => 'affiliation',
            'default_amount' => 3000.00,
            'applies_to' => 'new_enrollment',
        ]);
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Verification Fee',
            'code' => 'VERIFICATION_CNA',
            'category' => 'examination',
            'default_amount' => 2000.00,
            'applies_to' => 'new_enrollment',
        ]);
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Examination Fee',
            'code' => 'EXAM_CNA',
            'category' => 'examination',
            'default_amount' => 5000.00,
            'applies_to' => 'both',
        ]);
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Miscellaneous Charges',
            'code' => 'MISC_CNA',
            'category' => 'miscellaneous',
            'default_amount' => 1000.00,
            'applies_to' => 'new_enrollment',
        ]);
    }

    public function test_standard_admission_enrollment_uses_fee_structure()
    {
        $structure = FeeStructure::create([
            'course_id' => $this->course->id,
            'total_fee' => 120000.00,
            'installment_count' => 12,
        ]);

        $admission = Admission::create([
            'applicant_name' => 'Sania Malik',
            'father_name' => 'Malik Riaz',
            'father_phone' => '03123456789',
            'dob' => '2005-06-15',
            'gender' => 'female',
            'cnic' => '35302-1234567-8',
            'phone' => '03009876543',
            'email' => 'sania@gmail.com',
            'address' => 'Okara City',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'status' => 'approved',
            'admission_date' => now()->toDateString(),
        ]);

        $student = EnrollmentService::enroll($admission);

        $this->assertNotNull($student);
        $account = StudentFeeAccount::where('student_id', $student->id)->first();
        $this->assertNotNull($account);

        // The fee account follows tuition only; the admission fee is included.
        $this->assertEquals(120000.00, $account->original_fee);

        // Verify count of installment vouchers
        $installments = FeeVoucher::where('student_id', $student->id)
            ->where('voucher_type', 'monthly_installment')
            ->get();

        $this->assertEquals(12, $installments->count());

        $this->assertEquals(12, FeeVoucher::where('student_id', $student->id)->count());
    }

    public function test_custom_admission_enrollment_uses_custom_fee_overrides()
    {
        $structure = FeeStructure::create([
            'course_id' => $this->course->id,
            'total_fee' => 120000.00,
            'installment_count' => 12,
        ]);

        $admission = Admission::create([
            'applicant_name' => 'Sania Malik',
            'father_name' => 'Malik Riaz',
            'father_phone' => '03123456789',
            'dob' => '2005-06-15',
            'gender' => 'female',
            'cnic' => '35302-1234567-8',
            'phone' => '03009876543',
            'email' => 'sania@gmail.com',
            'address' => 'Okara City',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'status' => 'approved',
            'admission_date' => now()->toDateString(),

            // Custom fee plan overrides with deliberately uneven, ordered rows.
            'custom_installment_count' => 3,
            'custom_admission_fee' => 8000.00,
            'custom_tuition_fee' => 108000.00,
            'custom_verification_fee' => 1500.00,
            'custom_enrollment_fee' => 2500.00,
            'custom_examination_fee' => 4000.00,
            'custom_other_misc' => 500.00,
            'custom_installments' => [
                ['title' => 'Registration Installment', 'amount' => 25000.00, 'due_date' => '2026-09-15'],
                ['title' => 'Second Custom Installment', 'amount' => 33000.00, 'due_date' => '2026-10-20'],
                ['title' => 'Final Custom Installment', 'amount' => 42000.00, 'due_date' => '2026-11-25'],
            ],
        ]);

        $student = EnrollmentService::enroll($admission);

        $this->assertNotNull($student);
        $account = StudentFeeAccount::where('student_id', $student->id)->first();
        $this->assertNotNull($account);

        // Additional breakdown values are informational; tuition drives the account.
        $this->assertEquals(108000.00, $account->original_fee);

        $installments = FeeVoucher::where('student_id', $student->id)
            ->where('voucher_type', 'monthly_installment')
            ->get();

        $this->assertEquals(3, $installments->count());
        $this->assertEquals(4, FeeVoucher::where('student_id', $student->id)->count());
        $this->assertDatabaseHas('fee_vouchers', [
            'student_id' => $student->id,
            'voucher_type' => 'new_enrollment',
            'title' => 'Admission Fee',
            'total_amount' => 8000.00,
        ]);

        $tuitionVouchers = FeeVoucher::where('student_id', $student->id)
            ->where('voucher_type', 'monthly_installment')
            ->orderBy('id')
            ->get();

        $this->assertSame([
            'Registration Installment',
            'Second Custom Installment',
            'Final Custom Installment',
        ], $tuitionVouchers->pluck('title')->all());
        $this->assertSame([
            '2026-09-15',
            '2026-10-20',
            '2026-11-25',
        ], $tuitionVouchers->pluck('due_date')->map->toDateString()->all());
        $this->assertSame([
            '25000.00',
            '33000.00',
            '42000.00',
        ], $tuitionVouchers->pluck('total_amount')->all());

        $this->assertSame([
            'Registration Installment',
        ], $tuitionVouchers->first()->items()->orderBy('id')->pluck('description')->all());
        $this->assertEquals(108000.00, FeeVoucher::where('student_id', $student->id)->sum('total_amount'));

        // The guarded repair path can restore a legacy combined first voucher.
        $tuitionVouchers->first()->update([
            'title' => 'Admission & First Installment',
            'subtotal' => 33500,
            'total_amount' => 33500,
            'balance_amount' => 33500,
        ]);
        $account->update(['original_fee' => 116500, 'net_payable' => 116500, 'balance' => 116500]);
        app(AdmissionVoucherReconciliationService::class)->reconcile($account->fresh());

        $this->assertDatabaseHas('fee_vouchers', [
            'id' => $tuitionVouchers->first()->id,
            'title' => 'Registration Installment',
            'subtotal' => 25000,
            'total_amount' => 25000,
        ]);
        $this->assertEquals(108000.00, $account->fresh()->original_fee);
        $this->assertSame(200, FeeVoucherPdfService::streamBook($admission)->getStatusCode());

        $feePlan = app(AdmissionFeeAgreementData::class)->build($admission);
        $agreement = view('pdf.admission-agreement', [
            'admission' => $admission->load(['campus', 'course', 'academicSession']),
            'studentPhotoDataUri' => null,
            'feePlan' => $feePlan,
        ])->render();
        $this->assertStringContainsString('Registration Installment', $agreement);
        $this->assertStringContainsString('25,000.00', $agreement);
        $this->assertStringNotContainsString('<td class="data-label">Admission Fee</td>', $agreement);

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($admin);

        $completionScreen = view('admissions.complete', [
            'admission' => $admission->load(['student', 'campus', 'course', 'academicSession']),
            'vouchers' => $tuitionVouchers,
            'feeSnapshot' => null,
        ])->render();

        $this->assertStringContainsString('Review &amp; Edit Admission', $completionScreen);
        $this->assertStringContainsString(
            "/admin/admissions/{$admission->id}/edit?review=1",
            html_entity_decode($completionScreen),
        );
    }

    public function test_enrollment_normalizes_a_stale_schedule_for_another_campus(): void
    {
        FeeStructure::create([
            'course_id' => $this->course->id,
            'campus_id' => null,
            'academic_session_id' => null,
            'total_fee' => 100000.00,
            'installment_count' => 12,
            'status' => 'active',
        ]);
        $chichawatni = Campus::create([
            'name' => 'Daniyal College Chichawatni',
            'city' => 'Chichawatni',
            'is_active' => true,
        ]);
        $staleRows = collect(range(1, 12))->map(fn (int $number): array => [
            'title' => "Tuition Installment #{$number}",
            'amount' => '12500.00',
            'due_date' => now()->addMonths($number - 1)->toDateString(),
        ])->all();
        $admission = Admission::create([
            'applicant_name' => 'Cross Campus Applicant',
            'father_name' => 'Parent Name',
            'dob' => '2005-06-15',
            'gender' => 'female',
            'cnic' => '36501-1234567-8',
            'phone' => '03009876543',
            'address' => 'Chichawatni',
            'campus_id' => $chichawatni->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'status' => 'pending',
            'admission_date' => now()->toDateString(),
            'custom_tuition_fee' => 100000.00,
            'concession_type' => 'special',
            'concession_amount' => 15000.00,
            'concession_status' => 'approved',
            'custom_admission_fee' => 25000.00,
            'custom_installment_count' => 5,
            'custom_installment_interval_months' => 1,
            'custom_installment_start_date' => now()->toDateString(),
            'custom_installments' => $staleRows,
        ]);

        $student = EnrollmentService::enroll($admission);

        $this->assertSame($chichawatni->id, $student->campus_id);
        $this->assertCount(5, $admission->fresh()->custom_installments);
        $this->assertSame(60000.0, (float) collect($admission->fresh()->custom_installments)->sum('amount'));
        $this->assertSame(
            ['12000.00', '12000.00', '12000.00', '12000.00', '12000.00'],
            collect($admission->fresh()->custom_installments)->pluck('amount')->all(),
        );
        $this->assertSame(5, FeeVoucher::where('student_id', $student->id)
            ->where('voucher_type', 'monthly_installment')->count());
        $this->assertSame(60000.0, (float) FeeVoucher::where('student_id', $student->id)
            ->where('voucher_type', 'monthly_installment')->sum('subtotal'));
    }

    public function test_discount_and_admission_fee_are_separated_from_tuition_installments(): void
    {
        FeeStructure::create([
            'course_id' => $this->course->id,
            'total_fee' => 100000.00,
            'installment_count' => 5,
        ]);

        $admission = Admission::create([
            'applicant_name' => 'Fee Plan Student',
            'father_name' => 'Parent Name',
            'dob' => '2005-06-15',
            'gender' => 'female',
            'cnic' => '35302-7654321-8',
            'phone' => '03001112222',
            'address' => 'Okara City',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'status' => 'approved',
            'admission_date' => '2026-09-01',
            'concession_amount' => 20000,
            'custom_tuition_fee' => 100000,
            'custom_admission_fee' => 15000,
            'custom_installment_count' => 5,
            'custom_installment_interval_months' => 2,
            'custom_installment_start_date' => '2026-09-10',
            'custom_installments' => AdmissionResource::buildInstallmentRows(5, 65000, '2026-09-10', 2),
        ]);

        $student = EnrollmentService::enroll($admission);
        $account = $student->feeAccount;
        $admissionVoucher = FeeVoucher::where('student_id', $student->id)->where('voucher_type', 'new_enrollment')->firstOrFail();
        $installments = FeeVoucher::where('student_id', $student->id)->where('voucher_type', 'monthly_installment')->orderBy('due_date')->get();

        $this->assertSame(80000.0, (float) $account->net_payable);
        $this->assertSame('15000.00', $admissionVoucher->total_amount);
        $this->assertNull($admissionVoucher->installment_id);
        $this->assertSame(65000.0, (float) $installments->sum('total_amount'));
        $this->assertSame(['2026-09-10', '2026-11-10', '2027-01-10', '2027-03-10', '2027-05-10'], $installments->pluck('due_date')->map->toDateString()->all());
    }

    public function test_reconciliation_preserves_admission_payment_and_syncs_unpaid_tuition_schedule(): void
    {
        FeeStructure::create([
            'course_id' => $this->course->id,
            'total_fee' => 85000,
            'installment_count' => 4,
        ]);

        $admission = Admission::create([
            'applicant_name' => 'Existing Paid Student',
            'father_name' => 'Parent Name',
            'dob' => '2005-06-15',
            'gender' => 'female',
            'cnic' => '35302-1111111-8',
            'phone' => '03001112222',
            'address' => 'Okara City',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'status' => 'approved',
            'admission_date' => '2026-09-01',
            'custom_tuition_fee' => 100000,
            'custom_admission_fee' => 10000,
            'custom_installment_count' => 4,
            'custom_installments' => [
                ['title' => 'October Tuition', 'amount' => 15000, 'due_date' => '2026-10-10'],
                ['title' => 'December Tuition', 'amount' => 25000, 'due_date' => '2026-12-10'],
                ['title' => 'February Tuition', 'amount' => 25000, 'due_date' => '2027-02-10'],
                ['title' => 'Final Tuition', 'amount' => 25000, 'due_date' => '2027-04-10'],
            ],
        ]);

        $student = EnrollmentService::enroll($admission);
        $account = $student->feeAccount;
        $admissionVoucher = $account->vouchers()->where('voucher_type', 'new_enrollment')->firstOrFail();
        $collector = User::factory()->create();
        $payment = FeePayment::create([
            'student_id' => $student->id,
            'student_fee_account_id' => $account->id,
            'fee_voucher_id' => $admissionVoucher->id,
            'receipt_number' => 'TEST-RECEIPT-1',
            'amount' => 5000,
            'status' => 'paid',
            'payment_date' => '2026-09-01',
            'payment_method' => 'cash',
            'collected_by' => $collector->id,
        ]);
        PaymentAllocation::create([
            'payment_id' => $payment->id,
            'fee_voucher_id' => $admissionVoucher->id,
            'amount' => 5000,
        ]);
        $admissionVoucher->update(['paid_amount' => 5000, 'balance_amount' => 5000, 'status' => 'partially_paid']);
        $account->update(['amount_paid' => 5000, 'balance' => 95000]);

        // Simulate legacy voucher labels and dates without touching the paid row.
        $account->vouchers()->where('voucher_type', 'monthly_installment')->get()->each(
            fn (FeeVoucher $voucher, int $index) => $voucher->update([
                'title' => 'Legacy Installment #'.($index + 1),
                'due_date' => now()->setDate(2026, 9, 30)->addMonths($index),
            ])
        );

        app(AdmissionVoucherReconciliationService::class)->reconcile($account->fresh());

        $this->assertDatabaseHas('fee_payments', ['id' => $payment->id, 'amount' => 5000]);
        $this->assertDatabaseHas('fee_vouchers', [
            'id' => $admissionVoucher->id,
            'title' => 'Admission Fee',
            'total_amount' => 10000,
            'paid_amount' => 5000,
            'balance_amount' => 5000,
            'status' => 'partially_paid',
        ]);
        $this->assertSame(
            ['October Tuition', 'December Tuition', 'February Tuition', 'Final Tuition'],
            $account->vouchers()->where('voucher_type', 'monthly_installment')->orderBy('due_date')->pluck('title')->all(),
        );
        $this->assertSame(5000.0, (float) $account->fresh()->amount_paid);
        $this->assertSame(95000.0, (float) $account->fresh()->balance);
    }

    public function test_tuition_voucher_changes_redistribute_only_the_admission_tuition_plan(): void
    {
        FeeStructure::create([
            'course_id' => $this->course->id,
            'total_fee' => 100000,
            'installment_count' => 3,
        ]);
        $admission = Admission::create([
            'applicant_name' => 'Redistribution Student',
            'father_name' => 'Parent Name',
            'dob' => '2005-06-15',
            'gender' => 'female',
            'cnic' => '35302-2222222-8',
            'phone' => '03002223333',
            'address' => 'Okara City',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'status' => 'approved',
            'admission_date' => '2026-09-01',
            'custom_tuition_fee' => 100000,
            'custom_admission_fee' => 10000,
            'custom_installment_count' => 3,
            'custom_installments' => [
                ['title' => 'Tuition #1', 'amount' => 20000, 'due_date' => '2026-10-10'],
                ['title' => 'Tuition #2', 'amount' => 35000, 'due_date' => '2026-11-10'],
                ['title' => 'Tuition #3', 'amount' => 35000, 'due_date' => '2026-12-10'],
            ],
        ]);
        $student = EnrollmentService::enroll($admission);
        $vouchers = $student->feeAccount->vouchers()->where('voucher_type', 'monthly_installment')->orderBy('due_date')->get();
        $first = $vouchers->first()->load('items.feeHead');
        $first->items->first()->update(['unit_amount' => 15000, 'amount' => 15000]);
        $first->refresh()->update(FeeVoucherCalculator::calculate($first->fresh()));

        app(TuitionVoucherDistributionService::class)->rebalance($first->fresh());

        $this->assertSame(
            ['15000.00', '37500.00', '37500.00'],
            $student->feeAccount->vouchers()->where('voucher_type', 'monthly_installment')->orderBy('due_date')->pluck('total_amount')->all(),
        );
        $this->assertSame(
            ['15000.00', '37500.00', '37500.00'],
            collect($admission->fresh()->custom_installments)->pluck('amount')->all(),
        );

        $examHead = FeeHead::where('code', 'EXAM_CNA')->firstOrFail();
        $examVoucher = FeeVoucher::create([
            'student_id' => $student->id,
            'admission_id' => $admission->id,
            'student_fee_account_id' => $student->feeAccount->id,
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'voucher_number' => 'TEST-EXAM-001',
            'sequence_no' => 999,
            'title' => 'Examination Fee',
            'voucher_type' => 'examination_fee',
            'due_date' => '2027-01-10',
            'subtotal' => 5000,
            'total_amount' => 5000,
            'balance_amount' => 5000,
            'status' => 'issued',
        ]);
        $examVoucher->items()->create([
            'fee_head_id' => $examHead->id,
            'description' => 'Examination Fee',
            'quantity' => 1,
            'unit_amount' => 5000,
            'amount' => 5000,
            'adjustment_type' => 'debit',
        ]);
        app(TuitionVoucherDistributionService::class)->rebalance($examVoucher->fresh());

        $this->assertSame(
            ['15000.00', '37500.00', '37500.00'],
            collect($admission->fresh()->custom_installments)->pluck('amount')->all(),
        );
        $this->assertDatabaseHas('fee_vouchers', [
            'id' => $examVoucher->id,
            'voucher_type' => 'examination_fee',
            'total_amount' => 5000,
        ]);
    }

    public function test_legacy_combined_admission_row_is_normalized_before_editing(): void
    {
        $page = new class extends EditAdmission
        {
            public function normalize(array $data): array
            {
                return parent::mutateFormDataBeforeFill($data);
            }
        };

        $data = $page->normalize([
            'admission_date' => '2026-08-31',
            'custom_tuition_fee' => 100000,
            'concession_amount' => 10000,
            'custom_admission_fee' => 0,
            'custom_installment_count' => 5,
            'custom_installments' => [
                ['title' => 'Admission fee', 'amount' => 10000, 'due_date' => '2026-08-31'],
                ['title' => 'Installment #2', 'amount' => 20000, 'due_date' => '2026-10-10'],
                ['title' => 'Installment #3', 'amount' => 20000, 'due_date' => '2026-11-10'],
                ['title' => 'Installment #4', 'amount' => 20000, 'due_date' => '2026-12-10'],
                ['title' => 'Installment #5', 'amount' => 20000, 'due_date' => '2027-01-10'],
            ],
        ]);

        $this->assertSame(10000.0, $data['custom_admission_fee']);
        $this->assertSame(4, $data['custom_installment_count']);
        $this->assertSame('2026-10-10', $data['custom_installment_start_date']);
        $this->assertSame(80000.0, (float) collect($data['custom_installments'])->sum('amount'));
        $this->assertStringNotContainsString('admission', strtolower($data['custom_installments'][0]['title']));
    }

    public function test_reconciliation_retires_extra_unpaid_tuition_vouchers_and_preserves_other_fee_heads(): void
    {
        FeeStructure::create([
            'course_id' => $this->course->id,
            'total_fee' => 100000,
            'installment_count' => 5,
        ]);

        $admission = Admission::create([
            'applicant_name' => 'Legacy Schedule Student',
            'father_name' => 'Parent Name',
            'dob' => '2005-06-15',
            'gender' => 'female',
            'cnic' => '35302-9999999-8',
            'phone' => '03009998888',
            'address' => 'Okara City',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'status' => 'approved',
            'admission_date' => '2026-09-01',
            'custom_tuition_fee' => 85000,
            'custom_admission_fee' => 0,
            'custom_installment_count' => 5,
            'custom_installments' => collect(range(1, 5))->map(fn (int $number): array => [
                'title' => "Tuition Installment #{$number}",
                'amount' => 17000,
                'due_date' => now()->setDate(2026, 9, 10)->addMonths($number - 1)->toDateString(),
            ])->all(),
        ]);

        $student = EnrollmentService::enroll($admission);
        $account = $student->feeAccount;

        $legacyAdmissionVoucher = FeeVoucher::create([
            'student_id' => $student->id,
            'admission_id' => $admission->id,
            'student_fee_account_id' => $account->id,
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'voucher_number' => 'LEGACY-ADMISSION-001',
            'sequence_no' => 90,
            'title' => 'Admission & First Month Dues',
            'voucher_type' => 'new_enrollment',
            'due_date' => '2026-07-30',
            'subtotal' => 49333.33,
            'total_amount' => 49333.33,
            'balance_amount' => 49333.33,
            'status' => 'issued',
        ]);

        foreach (range(6, 11) as $number) {
            FeeVoucher::create([
                'student_id' => $student->id,
                'admission_id' => $admission->id,
                'student_fee_account_id' => $account->id,
                'campus_id' => $this->campus->id,
                'course_id' => $this->course->id,
                'academic_session_id' => $this->session->id,
                'voucher_number' => "LEGACY-TUITION-{$number}",
                'sequence_no' => 100 + $number,
                'title' => "Legacy Tuition Installment #{$number}",
                'voucher_type' => 'monthly_installment',
                'due_date' => now()->setDate(2027, 2, 10)->addMonths($number - 6),
                'subtotal' => 8333.33,
                'total_amount' => 8333.33,
                'balance_amount' => 8333.33,
                'status' => 'upcoming',
                'metadata' => ['source' => 'legacy', 'fee_component' => 'tuition_installment'],
            ]);
        }

        $examHead = FeeHead::where('code', 'EXAM_CNA')->firstOrFail();
        $examVoucher = FeeVoucher::create([
            'student_id' => $student->id,
            'admission_id' => $admission->id,
            'student_fee_account_id' => $account->id,
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'voucher_number' => 'LEGACY-EXAM-001',
            'sequence_no' => 999,
            'title' => 'Examination Registration Dues',
            'voucher_type' => 'monthly_installment',
            'due_date' => '2026-12-10',
            'subtotal' => 18000,
            'total_amount' => 18000,
            'balance_amount' => 18000,
            'status' => 'upcoming',
        ]);
        $examVoucher->items()->create([
            'fee_head_id' => $examHead->id,
            'description' => 'Examination Registration Dues',
            'quantity' => 1,
            'unit_amount' => 18000,
            'amount' => 18000,
        ]);

        app(AdmissionVoucherReconciliationService::class)->reconcile($account->fresh());

        $activeVouchers = $account->vouchers()
            ->with('items.feeHead')
            ->whereNotIn('status', ['cancelled', 'void'])
            ->get();

        $this->assertCount(5, $activeVouchers->filter->isAdmissionTuitionInstallment());
        $this->assertSame(7, $account->vouchers()->where('status', 'cancelled')->count());
        $this->assertDatabaseHas('fee_vouchers', [
            'id' => $legacyAdmissionVoucher->id,
            'status' => 'cancelled',
            'balance_amount' => 0,
        ]);
        $this->assertDatabaseHas('fee_vouchers', [
            'id' => $examVoucher->id,
            'status' => 'upcoming',
            'total_amount' => 18000,
        ]);
        $this->assertSame(85000.0, (float) $activeVouchers->filter->isAdmissionTuitionInstallment()->sum('total_amount'));
    }

    public function test_editing_one_installment_redistributes_the_remaining_tuition(): void
    {
        $rows = [
            'first' => ['title' => 'Installment #1', 'amount' => '15000.00'],
            'second' => ['title' => 'Installment #2', 'amount' => '20000.00'],
            'third' => ['title' => 'Installment #3', 'amount' => '20000.00'],
            'fourth' => ['title' => 'Installment #4', 'amount' => '20000.00'],
        ];

        $adjusted = AdmissionResource::redistributeInstallmentAmounts($rows, 'first', 80000);

        $this->assertSame('15000.00', $adjusted['first']['amount']);
        $this->assertSame('21666.66', $adjusted['second']['amount']);
        $this->assertSame('21666.66', $adjusted['third']['amount']);
        $this->assertSame('21666.68', $adjusted['fourth']['amount']);
        $this->assertSame(80000.0, (float) collect($adjusted)->sum('amount'));

        $saved = AdmissionResource::rebalanceEditedInstallmentAmounts([
            ['amount' => '15000.00'],
            ['amount' => '20000.00'],
            ['amount' => '20000.00'],
            ['amount' => '20000.00'],
        ], 80000);
        $this->assertSame(['15000.00', '21666.66', '21666.66', '21666.68'], collect($saved)->pluck('amount')->all());
    }

    public function test_orphan_legacy_fee_account_returns_validation_instead_of_crashing(): void
    {
        $admission = Admission::create([
            'applicant_name' => 'Legacy Applicant',
            'father_name' => 'Parent Name',
            'dob' => '2005-01-01',
            'gender' => 'female',
            'cnic' => '36501-1234567-1',
            'phone' => '03001234567',
            'address' => 'Chichawatni',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'admission_date' => now()->toDateString(),
            'status' => 'approved',
            'custom_tuition_fee' => 100000,
            'custom_installment_count' => 1,
            'custom_installments' => [[
                'title' => 'Tuition Installment #1',
                'amount' => '100000.00',
                'due_date' => now()->toDateString(),
            ]],
        ]);
        $account = StudentFeeAccount::create([
            'student_id' => 999999,
            'admission_id' => $admission->id,
            'original_fee' => 100000,
            'net_payable' => 100000,
            'balance' => 100000,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('legacy fee account is not linked to an active student');

        app(AdmissionVoucherReconciliationService::class)->reconcile($account);
    }
}
