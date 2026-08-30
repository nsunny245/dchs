<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\FeeVoucher;
use App\Models\StudentFeeAccount;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\Fees\FeeVoucherPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        // Expected original fee: 120000 (tuition) + 10000 (admission) + 3000 (enrollment) + 2000 (verification) + 5000 (exam) + 1000 (misc) = 141000.00
        $this->assertEquals(141000.00, $account->original_fee);

        // Verify count of installment vouchers
        $installments = FeeVoucher::where('student_id', $student->id)
            ->where('voucher_type', 'monthly_installment')
            ->get();

        // 11 tuition installments (vouchers 2 to 12) + 1 exam voucher = 12 monthly installment vouchers total
        $this->assertEquals(12, $installments->count());

        // Total vouchers count = 13 (1 new_enrollment + 12 monthly_installment)
        $this->assertEquals(13, FeeVoucher::where('student_id', $student->id)->count());
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
                ['title' => 'Final Custom Installment', 'amount' => 50000.00, 'due_date' => '2026-11-25'],
            ],
        ]);

        $student = EnrollmentService::enroll($admission);

        $this->assertNotNull($student);
        $account = StudentFeeAccount::where('student_id', $student->id)->first();
        $this->assertNotNull($account);

        // Expected custom original fee: 108000 (tuition) + 8000 (admission) + 2500 (enrollment) + 1500 (verification) + 4000 (exam) + 500 (misc) = 124500.00
        $this->assertEquals(124500.00, $account->original_fee);

        $installments = FeeVoucher::where('student_id', $student->id)
            ->where('voucher_type', 'monthly_installment')
            ->get();

        // Two remaining tuition vouchers plus the separately scheduled exam voucher.
        $this->assertEquals(3, $installments->count());
        $this->assertEquals(4, FeeVoucher::where('student_id', $student->id)->count());

        $tuitionVouchers = FeeVoucher::where('student_id', $student->id)
            ->where('title', '!=', 'Examination Registration Dues')
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
            '37500.00',
            '33000.00',
            '50000.00',
        ], $tuitionVouchers->pluck('total_amount')->all());

        $this->assertSame([
            'Admission Fee',
            'Registration Installment',
            'Enrollment Fee',
            'Verification Fee',
            'Miscellaneous / Other Charges',
        ], $tuitionVouchers->first()->items()->orderBy('id')->pluck('description')->all());
        $this->assertEquals(124500.00, FeeVoucher::where('student_id', $student->id)->sum('total_amount'));
        $this->assertSame(200, FeeVoucherPdfService::streamBook($admission)->getStatusCode());
    }
}
