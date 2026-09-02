<?php

namespace Tests\Feature;

use App\Filament\Resources\FeeVoucherResource\Pages\CreateFeeVoucher;
use App\Models\AcademicSession;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\FeeVoucher;
use App\Models\FeeVoucherItem;
use App\Models\Student;
use App\Models\StudentFeeAccount;
use App\Models\User;
use App\Policies\FeeVoucherPolicy;
use App\Services\Fees\FeeVoucherCalculator;
use App\Services\Fees\FeeVoucherService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FeeVoucherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard campus, course, session, user
        $this->campus = Campus::create([
            'name' => 'Test Campus',
            'city' => 'Lahore',
            'address' => '123 Test St',
            'phone' => '03001234567',
            'email' => 'campus@dchs.edu.pk',
            'is_active' => true,
        ]);

        $this->course = Course::create([
            'name' => 'FSc Pre-Engineering',
            'code' => 'FSC-PE',
            'duration_months' => 24,
            'eligibility' => 'Matric',
            'description' => 'Test Course',
            'is_active' => true,
        ]);

        $this->session = AcademicSession::create([
            'name' => '2026-2028',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Finance Officer',
            'email' => 'finance@dchs.edu.pk',
            'password' => bcrypt('password'),
            'campus_id' => $this->campus->id,
            'status' => true,
        ]);

        // Seed FSC-PE fee heads
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Admission Fee',
            'code' => 'ADMISSION_FSC-PE',
            'category' => 'admission',
            'default_amount' => 10000.00,
            'applies_to' => 'new_enrollment',
        ]);
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Enrollment Fee',
            'code' => 'ENDOWMENT_FSC-PE',
            'category' => 'affiliation',
            'default_amount' => 3000.00,
            'applies_to' => 'new_enrollment',
        ]);
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Verification Fee',
            'code' => 'VERIFICATION_FSC-PE',
            'category' => 'examination',
            'default_amount' => 2000.00,
            'applies_to' => 'new_enrollment',
        ]);
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Examination Fee',
            'code' => 'EXAM_FSC-PE',
            'category' => 'examination',
            'default_amount' => 5000.00,
            'applies_to' => 'both',
        ]);
        FeeHead::create([
            'course_id' => $this->course->id,
            'name' => 'Miscellaneous Charges',
            'code' => 'MISC_FSC-PE',
            'category' => 'miscellaneous',
            'default_amount' => 1000.00,
            'applies_to' => 'new_enrollment',
        ]);
    }

    protected function createAdmission(string $name = 'Test Student')
    {
        return Admission::create([
            'applicant_name' => $name,
            'father_name' => 'Test Father',
            'dob' => '2005-01-01',
            'gender' => 'male',
            'cnic' => '35202-'.rand(1000000, 9999999).'-1',
            'phone' => '0300'.rand(1000000, 9999999),
            'email' => strtolower(str_replace(' ', '', $name)).rand(100, 999).'@gmail.com',
            'address' => 'Test Address',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'academic_session_id' => $this->session->id,
            'status' => 'approved',
            'admission_date' => now()->toDateString(),
        ]);
    }

    protected function createStudentAndAccount(string $name = 'Test Student')
    {
        $admission = $this->createAdmission($name);

        $student = Student::create([
            'enrollment_number' => 'DGC-TCP-FSC-PE-2026-'.rand(100000, 999999),
            'full_name' => $name,
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'batch_year' => 2026,
            'status' => 'active',
            'admission_id' => $admission->id,
            'is_active' => true,
        ]);

        $account = StudentFeeAccount::create([
            'student_id' => $student->id,
            'admission_id' => $admission->id,
            'original_fee' => 0,
            'concession_amount' => 0,
            'net_payable' => 0,
            'amount_paid' => 0,
            'balance' => 0,
            'status' => 'active',
        ]);

        return [$student, $account, $admission];
    }

    public function test_voucher_calculations()
    {
        [$student, $account] = $this->createStudentAndAccount('Daniyal Saleem');

        // 1. Create a draft voucher
        $voucher = FeeVoucher::create([
            'voucher_number' => 'DGC-TES-2026-ENR-000001',
            'title' => 'Test Fee Voucher',
            'student_id' => $student->id,
            'student_fee_account_id' => $account->id,
            'voucher_type' => 'new_enrollment',
            'status' => 'draft',
            'campus_id' => $this->campus->id,
            'due_date' => '2026-08-01',
            'sequence_no' => 1,
            'total_amount' => 0.00,
            'balance_amount' => 0.00,
            'previous_balance' => 1500.00,
            'late_fee_amount' => 200.00,
            'discount_amount' => 500.00,
            'scholarship_amount' => 1000.00,
            'fine_amount' => 300.00,
        ]);

        $tuitionHead = FeeHead::create([
            'code' => 'TUITION',
            'name' => 'Tuition Fee',
            'category' => 'tuition',
            'applies_to' => 'both',
            'is_active' => true,
        ]);

        FeeVoucherItem::create([
            'fee_voucher_id' => $voucher->id,
            'fee_head_id' => $tuitionHead->id,
            'description' => 'Tuition Dues',
            'quantity' => 1,
            'unit_amount' => 10000.00,
            'amount' => 10000.00,
        ]);

        // Recalculate
        $totals = FeeVoucherCalculator::calculate($voucher);

        // Expected total payable:
        // subtotal (10000) + previous (1500) + late (200) + fine (300) - discount (500) - scholarship (1000)
        // = 10000 + 1500 + 200 + 300 - 500 - 1000 = 10500.00
        $this->assertEquals(10000.00, $totals['subtotal']);
        $this->assertEquals(10500.00, $totals['total_amount']);
        $this->assertEquals(10500.00, $totals['balance_amount']);
    }

    public function test_collision_safe_voucher_number_generation()
    {
        [$student, $account] = $this->createStudentAndAccount('Daniyal Saleem');

        // Generate voucher numbers concurrently/sequentially
        $v1 = FeeVoucherService::generateVoucherNumber($this->campus, 'new_enrollment', 2026);

        $this->assertEquals('DGC-TES-2026-ENR-000001', $v1['number']);
        $this->assertEquals(1, $v1['sequence']);

        // Insert first voucher into DB to test sequence increment
        FeeVoucher::create([
            'student_id' => $student->id,
            'student_fee_account_id' => $account->id,
            'voucher_number' => $v1['number'],
            'title' => 'Test Fee Voucher',
            'due_date' => '2026-08-01',
            'total_amount' => 0.00,
            'balance_amount' => 0.00,
            'sequence_no' => $v1['sequence'],
            'voucher_type' => 'new_enrollment',
            'campus_id' => $this->campus->id,
            'issue_date' => '2026-07-23',
        ]);

        $v2 = FeeVoucherService::generateVoucherNumber($this->campus, 'new_enrollment', 2026);
        $this->assertEquals('DGC-TES-2026-ENR-000002', $v2['number']);
        $this->assertEquals(2, $v2['sequence']);
    }

    public function test_enrollment_voucher_creation_workflow()
    {
        [$student, $account, $admission] = $this->createStudentAndAccount('Daniyal Saleem');

        $structure = FeeStructure::create([
            'course_id' => $this->course->id,
            'total_fee' => 120000.00,
            'installment_count' => 12,
        ]);

        $voucher = FeeVoucherService::generateEnrollmentVoucher($student, $admission, $structure);

        $this->assertNotNull($voucher);
        $this->assertEquals('monthly_installment', $voucher->voucher_type);
        $this->assertGreaterThan(0, $voucher->items->count());

        // Verify items were created correctly
        $this->assertCount(1, $voucher->items);
        $this->assertTrue($voucher->items->contains('description', 'Tuition Installment #1'));
    }

    public function test_duplicate_installment_voucher_prevention()
    {
        [$student, $account] = $this->createStudentAndAccount('Ali Raza');

        // Generate Installment #1
        $v1 = FeeVoucherService::generateInstallmentVoucher($student, 1, 10000.00);
        $this->assertNotNull($v1);

        // Attempting to generate same installment #1 should throw exception
        $this->expectException(\Exception::class);
        FeeVoucherService::generateInstallmentVoucher($student, 1, 10000.00);
    }

    public function test_campus_admin_can_edit_fee_voucher_without_prior_approval()
    {
        [$student, $account] = $this->createStudentAndAccount('Hassan Ali');

        $role = Role::firstOrCreate(['name' => 'Campus Principal']);

        $principal = User::create([
            'name' => 'Campus Principal',
            'email' => 'principal@dchs.edu.pk',
            'password' => bcrypt('password'),
            'campus_id' => $this->campus->id,
            'status' => true,
        ]);
        $principal->assignRole($role);

        $voucher = FeeVoucher::create([
            'voucher_number' => 'DGC-TES-2026-INS-000001',
            'title' => 'Monthly Installment',
            'student_id' => $student->id,
            'student_fee_account_id' => $account->id,
            'voucher_type' => 'monthly_installment',
            'status' => 'issued',
            'campus_id' => $this->campus->id,
            'due_date' => '2026-08-01',
            'sequence_no' => 1,
            'total_amount' => 5000.00,
            'balance_amount' => 5000.00,
        ]);

        $policy = new FeeVoucherPolicy;

        $this->assertTrue($policy->update($principal, $voucher));
    }

    public function test_custom_voucher_form_creates_and_synchronizes_fee_account(): void
    {
        [$student, $account] = $this->createStudentAndAccount('Custom Voucher Student');
        $tuitionHead = FeeHead::create([
            'course_id' => $this->course->id,
            'code' => 'TUITION_CUSTOM_FORM',
            'name' => 'Tuition Fee',
            'category' => 'tuition',
            'default_amount' => 10000,
            'applies_to' => 'monthly_installment',
            'is_active' => true,
        ]);
        $this->user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($this->user, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateFeeVoucher::class)
            ->assertDontSee('Unit Amount')
            ->fillForm([
                'student_id' => $student->id,
                'student_fee_account_id' => $account->id,
                'voucher_type' => 'monthly_installment',
                'campus_id' => $this->campus->id,
                'course_id' => $this->course->id,
                'academic_session_id' => $this->session->id,
                'admission_id' => $student->admission_id,
                'issue_date' => '2026-08-31',
                'due_date' => '2026-09-10',
                'orientation' => 'portrait_three_part',
                'discount_amount' => 500,
                'items' => [[
                    'fee_head_id' => $tuitionHead->id,
                    'description' => 'September Tuition',
                    'quantity' => 1,
                    'unit_amount' => 10000,
                    'amount' => 10000,
                    'adjustment_type' => 'debit',
                ]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $voucher = FeeVoucher::query()->where('student_id', $student->id)->firstOrFail();
        $this->assertSame('Custom Fee Voucher', $voucher->title);
        $this->assertSame($account->id, $voucher->student_fee_account_id);
        $this->assertSame('10000.00', $voucher->subtotal);
        $this->assertSame('9500.00', $voucher->total_amount);
        $this->assertSame('9500.00', $voucher->balance_amount);
        $this->assertDatabaseHas('fee_voucher_items', [
            'fee_voucher_id' => $voucher->id,
            'unit_amount' => 10000,
            'amount' => 10000,
        ]);
        $this->assertDatabaseHas('student_fee_accounts', [
            'id' => $account->id,
            'original_fee' => 10000,
            'net_payable' => 9500,
            'balance' => 9500,
        ]);
    }
}
