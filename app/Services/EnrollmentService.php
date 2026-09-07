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
use Illuminate\Support\Str;
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
            if ($student = Student::where('admission_id', $admission->id)->first()) {
                return $student;
            }

            $campus = $admission->campus;
            $course = $admission->course;
            $campusCode = strtoupper(substr($campus ? $campus->name : 'GEN', 0, 3));
            $courseCode = $course ? $course->code : 'GEN';
            $year = now()->year;

            // 2. Generate Enrollment Number
            $prefix = "DGC-{$campusCode}-{$courseCode}-{$year}-";
            $sequenceStart = strlen($prefix) + 1;
            $sequenceExpression = DB::getDriverName() === 'sqlite'
                ? "CAST(SUBSTR(enrollment_number, {$sequenceStart}) AS INTEGER)"
                : "CAST(SUBSTRING(enrollment_number, {$sequenceStart}) AS UNSIGNED)";
            $sequence = (int) (Student::withoutGlobalScopes()
                ->where('enrollment_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->max(DB::raw($sequenceExpression)) ?? 0) + 1;

            $seqFormatted = str_pad($sequence, 6, '0', STR_PAD_LEFT);
            $enrollmentNumber = $prefix.$seqFormatted;

            // 3. Create Corresponding User Account for Student login
            $email = $admission->email;
            if (! $email || User::where('email', $email)->exists()) {
                $sanitizedName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $admission->applicant_name)) ?: 'student';
                do {
                    $email = $sanitizedName.'.'.Str::lower(Str::random(8)).'@student.dgc.edu.pk';
                } while (User::where('email', $email)->exists());
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
            $totalPackage = round($tuitionTotal, 2);
            $concession = min(round((float) $admission->concession_amount, 2), $totalPackage);
            $netPayable = max(0, $totalPackage - $concession);
            $admissionFee = round(max(0, (float) $admission->custom_admission_fee), 2);

            if ($admissionFee > $netPayable) {
                throw ValidationException::withMessages([
                    'custom_admission_fee' => 'Admission fee cannot exceed discounted tuition of PKR '.number_format($netPayable, 2).'.',
                ]);
            }

            $remainingTuition = round($netPayable - $admissionFee, 2);
            $declaredInstallmentCount = $admission->custom_installment_count !== null
                ? (int) $admission->custom_installment_count
                : null;
            if ($declaredInstallmentCount !== null && ($declaredInstallmentCount < 1 || $declaredInstallmentCount > 12)) {
                throw ValidationException::withMessages([
                    'custom_installment_count' => $declaredInstallmentCount < 1
                        ? 'At least one installment is required before enrollment.'
                        : 'No more than 12 tuition installments may be generated.',
                ]);
            }

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
            $hasCustomSchedule = $declaredInstallmentCount !== null && $customSchedule->isNotEmpty();

            if ($hasCustomSchedule) {
                $scheduledTotal = round((float) $customSchedule->sum('amount'), 2);
                if ($customSchedule->count() !== $declaredInstallmentCount || abs($scheduledTotal - $remainingTuition) >= 0.01) {
                    $intervalMonths = max(1, min(5, (int) ($admission->custom_installment_interval_months ?: 1)));
                    $firstInstallmentDate = Carbon::parse(
                        $admission->custom_installment_start_date ?: $admission->admission_date ?: now(),
                    );
                    $customSchedule = collect(app(Fees\InstallmentPlanGenerator::class)->generate(
                        $remainingTuition,
                        $declaredInstallmentCount,
                        $firstInstallmentDate,
                        $intervalMonths,
                    ))->map(fn (array $row): array => [
                        'title' => $row['title'],
                        'amount' => round($row['gross_paisa'] / 100, 2),
                        'due_date' => Carbon::parse($row['due_date']),
                    ])->values();

                    // Persist the corrected source schedule in the same transaction
                    // so agreements, vouchers and fee collection can never diverge.
                    $admission->forceFill([
                        'custom_installments' => $customSchedule->map(fn (array $row): array => [
                            'title' => $row['title'],
                            'amount' => number_format($row['amount'], 2, '.', ''),
                            'due_date' => $row['due_date']->toDateString(),
                        ])->all(),
                    ])->save();
                }
            }

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
                : ($declaredInstallmentCount !== null
                ? $declaredInstallmentCount
                : ($structure->installment_count ?: 12));
            if ($installmentCount < 1) {
                throw ValidationException::withMessages([
                    'custom_installment_count' => 'At least one installment is required before enrollment.',
                ]);
            }
            if ($installmentCount > 12) {
                throw ValidationException::withMessages([
                    'custom_installment_count' => 'No more than 12 tuition installments may be generated.',
                ]);
            }
            if ($hasCustomSchedule && $installmentCount !== (int) $admission->custom_installment_count) {
                throw ValidationException::withMessages([
                    'custom_installments' => 'The saved installment rows must match the selected number of installments.',
                ]);
            }
            $monthlyTuition = $installmentCount > 0 ? round($remainingTuition / $installmentCount, 2) : 0.00;
            $intervalMonths = max(1, min(5, (int) ($admission->custom_installment_interval_months ?: 1)));
            $firstInstallmentDate = Carbon::parse(
                $admission->custom_installment_start_date ?: $admission->admission_date ?: now(),
            );
            $schedule = $remainingTuition <= 0
                ? collect()
                : ($hasCustomSchedule
                    ? $customSchedule
                    : collect(range(1, $installmentCount))->map(function (int $number) use ($firstInstallmentDate, $intervalMonths, $installmentCount, $monthlyTuition, $remainingTuition): array {
                        $amount = $number === $installmentCount
                            ? round($remainingTuition - ($monthlyTuition * ($installmentCount - 1)), 2)
                            : $monthlyTuition;

                        return [
                            'title' => "Tuition Installment #{$number}",
                            'amount' => $amount,
                            'due_date' => $firstInstallmentDate->copy()->addMonthsNoOverflow(($number - 1) * $intervalMonths),
                        ];
                    }));

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
            $firstVoucher = null;

            if ($admissionFee > 0) {
                $admissionHead = FeeHead::firstOrCreate(
                    ['code' => 'ADMISSION'],
                    [
                        'name' => 'Admission Fee',
                        'category' => 'admission',
                        'default_amount' => $admissionFee,
                        'applies_to' => 'new_enrollment',
                        'is_active' => true,
                        'sort_order' => 0,
                    ],
                );
                $voucherNumber = FeeVoucherService::generateVoucherNumber($campus, 'new_enrollment', $year);
                $firstVoucher = FeeVoucher::create([
                    'student_id' => $student->id,
                    'admission_id' => $admission->id,
                    'title' => 'Admission Fee',
                    'campus_id' => $student->campus_id,
                    'course_id' => $student->course_id,
                    'academic_session_id' => $admission->academic_session_id,
                    'fee_structure_id' => $structure->id,
                    'student_fee_account_id' => $feeAccount->id,
                    'voucher_number' => $voucherNumber['number'],
                    'voucher_type' => 'new_enrollment',
                    'orientation' => 'portrait_three_part',
                    'issue_date' => now(),
                    'due_date' => $admission->admission_date ?: now(),
                    'status' => 'issued',
                    'sequence_no' => $voucherNumber['sequence'],
                    'subtotal' => $admissionFee,
                    'discount_amount' => 0,
                    'total_amount' => $admissionFee,
                    'paid_amount' => 0,
                    'balance_amount' => $admissionFee,
                    'generated_by' => $actorId,
                    'metadata' => ['source' => 'admission_form', 'fee_component' => 'admission_fee'],
                ]);
                FeeVoucherItem::create([
                    'fee_voucher_id' => $firstVoucher->id,
                    'fee_head_id' => $admissionHead->id,
                    'description' => 'Admission Fee',
                    'quantity' => 1,
                    'unit_amount' => $admissionFee,
                    'amount' => $admissionFee,
                    'sort_order' => 0,
                ]);
            }

            foreach ($schedule as $index => $scheduledInstallment) {
                $tuitionAmount = round((float) $scheduledInstallment['amount'], 2);
                $installmentTitle = (string) $scheduledInstallment['title'];
                $dueDate = Carbon::parse($scheduledInstallment['due_date']);
                $issueDate = $dueDate->copy()->startOfMonth();
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
                    'status' => $admissionFee <= 0 && $index === 0 ? 'issued' : 'upcoming',
                    'sequence_no' => $voucherNumber['sequence'],
                    'subtotal' => $tuitionAmount,
                    'discount_amount' => 0,
                    'total_amount' => $tuitionAmount,
                    'paid_amount' => 0.00,
                    'balance_amount' => $tuitionAmount,
                    'generated_by' => $actorId,
                    'metadata' => ['source' => 'admission_form', 'fee_component' => 'tuition_installment'],
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
            if ($firstVoucher) {
                FeeVoucherAudit::create([
                    'fee_voucher_id' => $firstVoucher->id,
                    'user_id' => $actorId,
                    'action' => 'created',
                    'new_values' => ['student_id' => $student->id, 'enrollment_no' => $enrollmentNumber],
                    'ip_address' => request()->ip(),
                    'notes' => "Enrolled applicant {$admission->applicant_name} as student {$enrollmentNumber}. Vouchers generated.",
                ]);
            }

            return $student;
        });
    }
}
