<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Student;
use App\Models\StudentFeeAccount;
use App\Models\StudentFeeSnapshot;
use App\Models\StudentVoucher;
use App\Models\FeeStructure;
use App\Models\RebuildAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnrollmentService
{
    public static function enroll(Admission $admission, $actorId = null)
    {
        return DB::transaction(function () use ($admission, $actorId) {
            // 1. Lock the selected fee structure
            $structure = FeeStructure::where('course_id', $admission->course_id)
                ->where('campus_id', $admission->campus_id)
                ->first();

            if (!$structure) {
                throw new \Exception("No valid Fee Structure is assigned for this program and campus.");
            }

            // Check if already enrolled
            if ($admission->status === 'enrolled' || Student::where('admission_id', $admission->id)->exists()) {
                throw new \Exception("This applicant is already enrolled.");
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
            $enrollmentNumber = "DCHS-{$campusCode}-{$courseCode}-{$year}-{$seqFormatted}";

            // 3. Create Corresponding User Account for Student login
            $email = $admission->email;
            if (!$email || \App\Models\User::where('email', $email)->exists()) {
                $sanitizedName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $admission->applicant_name));
                $email = $sanitizedName . rand(1000, 9999) . '@student.dchs.edu.pk';
            }

            $userPassword = str_replace('-', '', $admission->cnic) ?: 'password';

            $user = \App\Models\User::create([
                'name' => $admission->applicant_name,
                'email' => $email,
                'password' => bcrypt($userPassword),
                'phone' => $admission->phone,
                'campus_id' => $admission->campus_id,
                'status' => true,
            ]);
            $user->assignRole('Student');

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

            // 4. Save Fee Structure Snapshot
            StudentFeeSnapshot::create([
                'student_id' => $student->id,
                'fee_structure_id' => $structure->id,
                'fee_structure_data' => $structure->toArray(),
            ]);

            // 5. Calculate Financial Dues
            $tuitionTotal = (float) $structure->total_fee;
            $admissionFee = (float) $structure->admission_fee;
            $enrollmentFee = (float) $structure->enrollment_fee;
            $verificationFee = (float) $structure->verification_fee;
            $examinationFee = (float) $structure->examination_fee;
            $otherMisc = (float) $structure->other_misc;

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

            // 6. Generate Vouchers
            $installmentCount = $structure->installment_count ?: 12;
            $monthlyTuition = $tuitionTotal / $installmentCount;

            $vouchers = [];

            // Voucher 1: First Month Tuition + Admission + Enrollment + Verification + Misc
            $firstVoucherAmount = $monthlyTuition + $admissionFee + $enrollmentFee + $verificationFee + $otherMisc;
            // Subtract concession from first voucher or split it? Let's apply it directly to first voucher, if concession exceeds first voucher, split remaining concession.
            $appliedConcession = min($concession, $firstVoucherAmount);
            $firstVoucherAmount -= $appliedConcession;
            $remainingConcession = $concession - $appliedConcession;

            $vouchers[] = [
                'student_id' => $student->id,
                'student_fee_account_id' => $feeAccount->id,
                'voucher_number' => "{$campusCode}-{$courseCode}-{$year}-{$seqFormatted}-01",
                'title' => 'Admission & First Month Dues',
                'due_date' => $admission->admission_date ?: now(),
                'amount' => $firstVoucherAmount,
                'paid_amount' => 0.00,
                'balance' => $firstVoucherAmount,
                'status' => $firstVoucherAmount <= 0 ? 'paid' : 'due',
                'sequence_no' => 1,
            ];

            // Vouchers 2 to N: Remaining Monthly Tuition Installments
            for ($i = 2; $i <= $installmentCount; $i++) {
                $voucherAmount = $monthlyTuition;
                if ($remainingConcession > 0) {
                    $applied = min($remainingConcession, $voucherAmount);
                    $voucherAmount -= $applied;
                    $remainingConcession -= $applied;
                }

                $dueDate = now()->parse($admission->admission_date ?: now())->addMonths($i - 1);

                $vouchers[] = [
                    'student_id' => $student->id,
                    'student_fee_account_id' => $feeAccount->id,
                    'voucher_number' => "{$campusCode}-{$courseCode}-{$year}-{$seqFormatted}-" . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'title' => "Tuition Installment #{$i}",
                    'due_date' => $dueDate,
                    'amount' => $voucherAmount,
                    'paid_amount' => 0.00,
                    'balance' => $voucherAmount,
                    'status' => $voucherAmount <= 0 ? 'paid' : 'upcoming',
                    'sequence_no' => $i,
                ];
            }

            // Voucher N+1: Examination Fee (typically due in 5 months)
            if ($examinationFee > 0) {
                $examDueDate = now()->parse($admission->admission_date ?: now())->addMonths(5);
                $examSeq = $installmentCount + 1;
                $vouchers[] = [
                    'student_id' => $student->id,
                    'student_fee_account_id' => $feeAccount->id,
                    'voucher_number' => "{$campusCode}-{$courseCode}-{$year}-{$seqFormatted}-EXAM",
                    'title' => 'Examination Registration Dues',
                    'due_date' => $examDueDate,
                    'amount' => $examinationFee,
                    'paid_amount' => 0.00,
                    'balance' => $examinationFee,
                    'status' => 'upcoming',
                    'sequence_no' => $examSeq,
                ];
            }

            // Save all vouchers
            foreach ($vouchers as $v) {
                StudentVoucher::create($v);
            }

            // 7. Finalize Admission status
            $admission->update([
                'status' => 'enrolled',
                'enrollment_no' => $enrollmentNumber,
            ]);

            // 8. Log Audit Record
            RebuildAuditLog::create([
                'user_id' => $actorId,
                'action' => 'student_enrollment',
                'description' => "Enrolled applicant {$admission->applicant_name} as student {$enrollmentNumber}. Total Package: PKR {$totalPackage}, Net Payable: PKR {$netPayable}.",
                'ip_address' => request()->ip(),
            ]);

            return $student;
        });
    }
}
