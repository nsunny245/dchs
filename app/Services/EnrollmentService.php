<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\FeeHead;
use App\Models\FeeVoucher;
use App\Models\FeeVoucherAudit;
use App\Models\FeeVoucherItem;
use App\Models\Student;
use App\Models\StudentFeeAccount;
use App\Models\StudentFeeSnapshot;
use App\Models\User;
use App\Services\Fees\FeeVoucherService;
use App\Services\Fees\OfficialFeeStructureResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class EnrollmentService
{
    public static function enroll(Admission $admission, $actorId = null)
    {
        return DB::transaction(function () use ($admission, $actorId) {
            // 1. Lock the selected fee structure
            $structure = app(OfficialFeeStructureResolver::class)->resolve(
                $admission->course_id,
                $admission->campus_id,
                $admission->academic_session_id,
                $admission->admission_date,
            );

            if (! $structure) {
                throw ValidationException::withMessages([
                    'course_id' => 'No active official fee plan exists for the selected campus, session, course, and admission date.',
                ]);
            }

            // Check if already enrolled
            if ($admission->status === 'enrolled' || Student::where('admission_id', $admission->id)->exists()) {
                throw new \Exception('This applicant is already enrolled.');
            }

            $campus = $admission->campus;
            $course = $admission->course;
            $campusCode = strtoupper(substr($campus ? $campus->name : 'GEN', 0, 3));
            $courseCode = $course ? $course->code : 'GEN';
            $year = now()->year;

            // 2. Generate Enrollment Number
            $sequence = Student::where('campus_id', $admission->campus_id)
                ->where('course_id', $admission->course_id)
                ->count() + 1;

            $seqFormatted = str_pad($sequence, 6, '0', STR_PAD_LEFT);
            $enrollmentNumber = "DGC-{$campusCode}-{$courseCode}-{$year}-{$seqFormatted}";

            // 3. Create Corresponding User Account for Student login
            $email = $admission->email;
            if (! $email || User::where('email', $email)->exists()) {
                $sanitizedName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $admission->applicant_name));
                $email = $sanitizedName.rand(1000, 9999).'@student.dgc.edu.pk';
            }

            $userPassword = str_replace('-', '', $admission->cnic) ?: 'password';

            $user = User::create([
                'name' => $admission->applicant_name,
                'email' => $email,
                'password' => bcrypt($userPassword),
                'phone' => $admission->phone,
                'campus_id' => $admission->campus_id,
                'status' => true,
            ]);
            try {
                $studentRole = Role::firstOrCreate(['name' => 'Student'], ['guard_name' => 'web']);
                $user->roles()->syncWithoutDetaching([$studentRole->id]);
            } catch (\Throwable $e) {
                // Ignore role assignment failure gracefully
            }

            // 4. Create Student
            $student = Student::create([
                'user_id' => $user->id,
                'enrollment_number' => $enrollmentNumber,
                'full_name' => $admission->applicant_name,
                'campus_id' => $admission->campus_id,
                'course_id' => $admission->course_id,
                'batch_year' => $year,
                'status' => 'active',
                'admission_id' => $admission->id,
                'enrollment_date' => now(),
                'is_active' => true,
                'franchisor_id' => $admission->franchisor_id,
            ]);

            // 5. Save Fee Structure Snapshot
            StudentFeeSnapshot::create([
                'student_id' => $student->id,
                'fee_structure_id' => $structure->id,
                'fee_structure_data' => $structure->toArray(),
                'admission_id' => $admission->id,
                'campus_id' => $admission->campus_id,
                'academic_session_id' => $admission->academic_session_id,
                'structure_version' => $structure->version,
            ]);

            // 6. Calculate Financial Dues
            $tuitionTotal = $admission->custom_installment_count !== null
                ? (float) $admission->custom_tuition_fee
                : (float) $structure->total_fee;

            if ($admission->custom_installment_count !== null) {
                $admissionFee = (float) $admission->custom_admission_fee;
                $enrollmentFee = (float) $admission->custom_enrollment_fee;
                $verificationFee = (float) $admission->custom_verification_fee;
                $examinationFee = (float) $admission->custom_examination_fee;
                $otherMisc = (float) $admission->custom_other_misc;
            } else {
                $admissionHead = FeeHead::where('course_id', $admission->course_id)->where('category', 'admission')->first();
                $admissionFee = (float) ($admissionHead?->default_amount ?: 0.00);

                $endowmentHead = FeeHead::where('course_id', $admission->course_id)->where('category', 'affiliation')->first();
                $enrollmentFee = (float) ($endowmentHead?->default_amount ?: 0.00);

                $verificationHead = FeeHead::where('course_id', $admission->course_id)->where('code', 'like', 'VERIFICATION_%')->first();
                $verificationFee = (float) ($verificationHead?->default_amount ?: 0.00);

                $examHead = FeeHead::where('course_id', $admission->course_id)->where('code', 'like', 'EXAM_%')->first();
                $examinationFee = (float) ($examHead?->default_amount ?: 0.00);

                $miscHead = FeeHead::where('course_id', $admission->course_id)->where('category', 'miscellaneous')->first();
                $hostelHead = FeeHead::where('course_id', $admission->course_id)->where('category', 'hostel')->first();
                $otherMisc = (float) ($miscHead?->default_amount ?: 0.00) + (float) ($hostelHead?->default_amount ?: 0.00);
            }

            $totalPackage = $tuitionTotal + $admissionFee + $enrollmentFee + $verificationFee + $examinationFee + $otherMisc;
            $concession = (float) $admission->concession_amount;
            $netPayable = $totalPackage - $concession;

            // Create Student Fee Account
            $feeAccount = StudentFeeAccount::create([
                'student_id' => $student->id,
                'admission_id' => $admission->id,
                'original_fee' => $totalPackage,
                'concession_amount' => $concession,
                'net_payable' => $netPayable,
                'amount_paid' => 0.00,
                'balance' => $netPayable,
                'status' => 'active',
            ]);

            // 7. Generate Vouchers
            $installmentCount = $admission->custom_installment_count !== null
                ? (int) $admission->custom_installment_count
                : ($structure->installment_count ?: 12);
            $monthlyTuition = $installmentCount > 0 ? ($tuitionTotal / $installmentCount) : 0.00;

            // Voucher 1: First Month Tuition + Admission + Enrollment + Verification + Misc
            $firstVoucherAmount = $monthlyTuition + $admissionFee + $enrollmentFee + $verificationFee + $otherMisc;
            $appliedConcession = min($concession, $firstVoucherAmount);
            $firstVoucherAmount -= $appliedConcession;
            $remainingConcession = $concession - $appliedConcession;
            $firstVoucherNumber = FeeVoucherService::generateVoucherNumber(
                $campus,
                'new_enrollment',
                $year,
            );

            $firstVoucher = FeeVoucher::create([
                'student_id' => $student->id,
                'admission_id' => $admission->id,
                'title' => 'Admission & First Month Dues',
                'campus_id' => $student->campus_id,
                'course_id' => $student->course_id,
                'academic_session_id' => $admission->academic_session_id,
                'fee_structure_id' => $structure->id,
                'student_fee_account_id' => $feeAccount->id,
                'voucher_number' => $firstVoucherNumber['number'],
                'voucher_type' => 'new_enrollment',
                'orientation' => 'horizontal_three_part',
                'issue_date' => now(),
                'due_date' => $admission->admission_date ?: now(),
                'status' => 'issued',
                'sequence_no' => $firstVoucherNumber['sequence'],
                'subtotal' => $monthlyTuition + $admissionFee + $enrollmentFee + $verificationFee + $otherMisc,
                'discount_amount' => $appliedConcession,
                'total_amount' => $firstVoucherAmount,
                'paid_amount' => 0.00,
                'balance_amount' => $firstVoucherAmount,
                'generated_by' => $actorId ?: 1,
            ]);

            // Add Fee Voucher Items for first voucher
            $order = 1;
            $firstVoucherItems = [
                ['code' => 'ADMISSION', 'name' => 'Admission Fee', 'amount' => $admissionFee, 'category' => 'admission'],
                ['code' => 'TUITION_1', 'name' => 'Tuition Fee / First Installment', 'amount' => $monthlyTuition, 'category' => 'tuition'],
                ['code' => 'ENROLLMENT', 'name' => 'Enrollment Fee', 'amount' => $enrollmentFee, 'category' => 'affiliation'],
                ['code' => 'VERIFICATION', 'name' => 'Verification Fee', 'amount' => $verificationFee, 'category' => 'examination'],
                ['code' => 'MISC', 'name' => 'Miscellaneous / Other Charges', 'amount' => $otherMisc, 'category' => 'miscellaneous'],
            ];

            foreach ($firstVoucherItems as $item) {
                if ($item['amount'] > 0) {
                    $head = FeeHead::firstOrCreate(
                        ['code' => $item['code']],
                        [
                            'name' => $item['name'],
                            'category' => $item['category'],
                            'default_amount' => $item['amount'],
                            'applies_to' => 'new_enrollment',
                            'is_active' => true,
                            'sort_order' => $order++,
                        ]
                    );

                    FeeVoucherItem::create([
                        'fee_voucher_id' => $firstVoucher->id,
                        'fee_head_id' => $head->id,
                        'description' => $head->name,
                        'quantity' => 1,
                        'unit_amount' => $item['amount'],
                        'amount' => $item['amount'],
                        'sort_order' => $head->sort_order,
                    ]);
                }
            }

            // Vouchers 2 to N: Remaining Monthly Tuition Installments
            for ($i = 2; $i <= $installmentCount; $i++) {
                $voucherAmount = $monthlyTuition;
                $discountForThisVoucher = 0.00;
                if ($remainingConcession > 0) {
                    $applied = min($remainingConcession, $voucherAmount);
                    $voucherAmount -= $applied;
                    $discountForThisVoucher = $applied;
                    $remainingConcession -= $applied;
                }

                $dueDate = now()->parse($admission->admission_date ?: now())->addMonths($i - 1);
                $installmentVoucherNumber = FeeVoucherService::generateVoucherNumber(
                    $campus,
                    'monthly_installment',
                    $year,
                );

                $instVoucher = FeeVoucher::create([
                    'student_id' => $student->id,
                    'admission_id' => $admission->id,
                    'title' => "Tuition Installment #{$i}",
                    'campus_id' => $student->campus_id,
                    'course_id' => $student->course_id,
                    'academic_session_id' => $admission->academic_session_id,
                    'fee_structure_id' => $structure->id,
                    'installment_id' => null,
                    'student_fee_account_id' => $feeAccount->id,
                    'voucher_number' => $installmentVoucherNumber['number'],
                    'voucher_type' => 'monthly_installment',
                    'orientation' => 'horizontal_three_part',
                    'issue_date' => now(),
                    'due_date' => $dueDate,
                    'status' => 'upcoming',
                    'sequence_no' => $installmentVoucherNumber['sequence'],
                    'subtotal' => $monthlyTuition,
                    'discount_amount' => $discountForThisVoucher,
                    'total_amount' => $voucherAmount,
                    'paid_amount' => 0.00,
                    'balance_amount' => $voucherAmount,
                    'generated_by' => $actorId ?: 1,
                ]);

                $tuitionHead = FeeHead::firstOrCreate(
                    ['code' => 'TUITION_REC'],
                    [
                        'name' => 'Monthly Fee / Installment',
                        'category' => 'tuition',
                        'default_amount' => $monthlyTuition,
                        'applies_to' => 'monthly_installment',
                        'is_active' => true,
                        'sort_order' => 1,
                    ]
                );

                FeeVoucherItem::create([
                    'fee_voucher_id' => $instVoucher->id,
                    'fee_head_id' => $tuitionHead->id,
                    'description' => "Tuition Installment #{$i}",
                    'quantity' => 1,
                    'unit_amount' => $monthlyTuition,
                    'amount' => $monthlyTuition,
                    'sort_order' => 1,
                ]);
            }

            // Examination registration is a separately due charge and therefore
            // remains an additional voucher outside the tuition installment count.
            if ($examinationFee > 0) {
                $examDueDate = now()->parse($admission->admission_date ?: now())->addMonths(5);
                $examVoucherNumber = FeeVoucherService::generateVoucherNumber(
                    $campus,
                    'monthly_installment',
                    $year,
                );
                $examDiscount = min($remainingConcession, $examinationFee);
                $examAmount = $examinationFee - $examDiscount;

                $examVoucher = FeeVoucher::create([
                    'student_id' => $student->id,
                    'admission_id' => $admission->id,
                    'title' => 'Examination Registration Dues',
                    'campus_id' => $student->campus_id,
                    'course_id' => $student->course_id,
                    'academic_session_id' => $admission->academic_session_id,
                    'fee_structure_id' => $structure->id,
                    'student_fee_account_id' => $feeAccount->id,
                    'voucher_number' => $examVoucherNumber['number'],
                    'voucher_type' => 'monthly_installment',
                    'orientation' => 'horizontal_three_part',
                    'issue_date' => now(),
                    'due_date' => $examDueDate,
                    'status' => 'upcoming',
                    'sequence_no' => $examVoucherNumber['sequence'],
                    'subtotal' => $examinationFee,
                    'discount_amount' => $examDiscount,
                    'total_amount' => $examAmount,
                    'paid_amount' => 0.00,
                    'balance_amount' => $examAmount,
                    'generated_by' => $actorId ?: 1,
                ]);

                $examHead = FeeHead::firstOrCreate(
                    ['code' => 'EXAM'],
                    [
                        'name' => 'Examination Fee',
                        'category' => 'examination',
                        'default_amount' => $examinationFee,
                        'applies_to' => 'both',
                        'is_active' => true,
                        'sort_order' => 1,
                    ],
                );

                FeeVoucherItem::create([
                    'fee_voucher_id' => $examVoucher->id,
                    'fee_head_id' => $examHead->id,
                    'description' => 'Examination Registration Dues',
                    'quantity' => 1,
                    'unit_amount' => $examinationFee,
                    'amount' => $examinationFee,
                    'sort_order' => 1,
                ]);
            }

            // 8. Finalize Admission status
            $admission->update([
                'status' => 'enrolled',
                'enrollment_no' => $enrollmentNumber,
            ]);

            // 9. Log Audit Record via audits table
            FeeVoucherAudit::create([
                'fee_voucher_id' => $firstVoucher->id,
                'user_id' => $actorId ?: 1,
                'action' => 'created',
                'new_values' => ['student_id' => $student->id, 'enrollment_no' => $enrollmentNumber],
                'ip_address' => request()->ip(),
                'notes' => "Enrolled applicant {$admission->applicant_name} as student {$enrollmentNumber}. Vouchers generated.",
            ]);

            return $student;
        });
    }
}
