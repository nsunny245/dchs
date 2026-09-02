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
use Illuminate\Support\Carbon;
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
            $customSchedule = collect($admission->custom_installments ?? [])
                ->map(function (array $installment, int $index) use ($admission): array {
                    $dueDate = filled($installment['due_date'] ?? null)
                        ? Carbon::parse($installment['due_date'])
                        : Carbon::parse($admission->admission_date ?: now())->addMonths($index);

                    return [
                        'title' => trim((string) ($installment['title'] ?? '')) ?: 'Tuition Installment #'.($index + 1),
                        'amount' => round(max(0, (float) ($installment['amount'] ?? 0)), 2),
                        'due_date' => $dueDate,
                    ];
                })
                ->filter(fn (array $installment): bool => $installment['amount'] > 0)
                ->values();
            $hasCustomSchedule = $admission->custom_installment_count !== null && $customSchedule->isNotEmpty();

            if ($hasCustomSchedule) {
                $scheduledTotal = round((float) $customSchedule->sum('amount'), 2);
                $declaredTuitionTotal = round((float) $admission->custom_tuition_fee, 2);

                if (abs($scheduledTotal - $declaredTuitionTotal) >= 0.01) {
                    throw ValidationException::withMessages([
                        'custom_installments' => 'Custom installment amounts must total PKR '.number_format($declaredTuitionTotal, 2).'. Current schedule total: PKR '.number_format($scheduledTotal, 2).'.',
                    ]);
                }
            }

            $tuitionTotal = $admission->custom_installment_count !== null
                ? (float) $admission->custom_tuition_fee
                : (float) $structure->total_fee;

            // The admission fee is already included in tuition. Examination,
            // verification and miscellaneous figures are agreement breakdowns,
            // not extra balances to merge into the first installment voucher.
            $totalPackage = round($tuitionTotal, 2);
            $concession = min(round((float) $admission->concession_amount, 2), $totalPackage);
            $netPayable = max(0, $totalPackage - $concession);

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
            $installmentCount = $hasCustomSchedule
                ? $customSchedule->count()
                : ($admission->custom_installment_count !== null
                ? (int) $admission->custom_installment_count
                : ($structure->installment_count ?: 12));
            $monthlyTuition = $installmentCount > 0 ? round($tuitionTotal / $installmentCount, 2) : 0.00;
            $schedule = $hasCustomSchedule
                ? $customSchedule
                : collect(range(1, $installmentCount))->map(function (int $number) use ($admission, $installmentCount, $monthlyTuition, $tuitionTotal): array {
                    $amount = $number === $installmentCount
                        ? round($tuitionTotal - ($monthlyTuition * ($installmentCount - 1)), 2)
                        : $monthlyTuition;

                    return [
                        'title' => "Tuition Installment #{$number}",
                        'amount' => $amount,
                        'due_date' => Carbon::parse($admission->admission_date ?: now())->addMonths($number - 1),
                    ];
                });

            $tuitionHead = FeeHead::firstOrCreate(
                ['code' => 'TUITION_REC'],
                [
                    'name' => 'Tuition Fee / Installment',
                    'category' => 'tuition',
                    'default_amount' => $monthlyTuition,
                    'applies_to' => 'monthly_installment',
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );
            $remainingConcession = $concession;
            $firstVoucher = null;

            foreach ($schedule as $index => $scheduledInstallment) {
                $tuitionAmount = round((float) $scheduledInstallment['amount'], 2);
                $installmentTitle = (string) $scheduledInstallment['title'];
                $discountForThisVoucher = min($remainingConcession, $tuitionAmount);
                $voucherAmount = max(0, $tuitionAmount - $discountForThisVoucher);
                $remainingConcession -= $discountForThisVoucher;
                $dueDate = Carbon::parse($scheduledInstallment['due_date']);
                $issueDate = $index === 0 ? now() : $dueDate->copy()->startOfMonth();
                $voucherNumber = FeeVoucherService::generateVoucherNumber($campus, 'monthly_installment', $year);

                $voucher = FeeVoucher::create([
                    'student_id' => $student->id,
                    'admission_id' => $admission->id,
                    'title' => $installmentTitle,
                    'campus_id' => $student->campus_id,
                    'course_id' => $student->course_id,
                    'academic_session_id' => $admission->academic_session_id,
                    'fee_structure_id' => $structure->id,
                    'installment_id' => null,
                    'student_fee_account_id' => $feeAccount->id,
                    'voucher_number' => $voucherNumber['number'],
                    'voucher_type' => 'monthly_installment',
                    'orientation' => 'portrait_three_part',
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'status' => $index === 0 ? 'issued' : 'upcoming',
                    'sequence_no' => $voucherNumber['sequence'],
                    'subtotal' => $tuitionAmount,
                    'discount_amount' => $discountForThisVoucher,
                    'total_amount' => $voucherAmount,
                    'paid_amount' => 0.00,
                    'balance_amount' => $voucherAmount,
                    'generated_by' => $actorId ?: 1,
                ]);

                FeeVoucherItem::create([
                    'fee_voucher_id' => $voucher->id,
                    'fee_head_id' => $tuitionHead->id,
                    'description' => $installmentTitle,
                    'quantity' => 1,
                    'unit_amount' => $tuitionAmount,
                    'amount' => $tuitionAmount,
                    'sort_order' => 1,
                ]);

                $firstVoucher ??= $voucher;
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
