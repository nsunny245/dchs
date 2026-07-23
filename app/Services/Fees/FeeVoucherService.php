<?php

namespace App\Services\Fees;

use App\Models\Admission;
use App\Models\FeeHead;
use App\Models\FeeVoucher;
use App\Models\FeeVoucherItem;
use App\Models\FeePayment;
use App\Models\FeeVoucherAudit;
use App\Models\Student;
use App\Models\StudentFeeAccount;
use App\Models\FeeStructure;
use Illuminate\Support\Facades\DB;

class FeeVoucherService
{
    /**
     * Generate a collision-safe voucher number.
     */
    public static function generateVoucherNumber($campus, $type, $year)
    {
        $campusCode = strtoupper(substr($campus->code ?? $campus->name, 0, 3));
        $typeCode = $type === 'new_enrollment' ? 'ENR' : 'INS';
        
        return DB::transaction(function () use ($campus, $campusCode, $typeCode, $year, $type) {
            $maxSeq = FeeVoucher::where('campus_id', $campus->id)
                ->where('voucher_type', $type)
                ->lockForUpdate()
                ->max('sequence_no') ?? 0;
            
            $nextSeq = $maxSeq + 1;
            $seqFormatted = str_pad($nextSeq, 6, '0', STR_PAD_LEFT);
            
            return [
                'number' => "DGC-{$campusCode}-{$year}-{$typeCode}-{$seqFormatted}",
                'sequence' => $nextSeq
            ];
        });
    }

    /**
     * Generate New Student Enrollment Voucher.
     */
    public static function generateEnrollmentVoucher(Student $student, Admission $admission, FeeStructure $structure, array $adjustments = []): FeeVoucher
    {
        return DB::transaction(function () use ($student, $admission, $structure, $adjustments) {
            $year = now()->format('Y');
            $campus = $student->campus;
            $voucherData = self::generateVoucherNumber($campus, 'new_enrollment', $year);

            // 1. Resolve student fee account
            $feeAccount = StudentFeeAccount::where('student_id', $student->id)->first();
            if (!$feeAccount) {
                $feeAccount = StudentFeeAccount::create([
                    'student_id' => $student->id,
                    'admission_id' => $admission->id,
                    'original_fee' => 0.00,
                    'concession_amount' => 0.00,
                    'net_payable' => 0.00,
                    'amount_paid' => 0.00,
                    'balance' => 0.00,
                    'status' => 'active',
                ]);
            }

            // 2. Create the Fee Voucher
            $voucher = FeeVoucher::create([
                'voucher_number' => $voucherData['number'],
                'title' => 'Admission & First Month Dues',
                'student_id' => $student->id,
                'admission_id' => $admission->id,
                'campus_id' => $student->campus_id,
                'course_id' => $student->course_id,
                'academic_session_id' => $admission->academic_session_id,
                'fee_structure_id' => $structure->id,
                'student_fee_account_id' => $feeAccount->id,
                'voucher_type' => 'new_enrollment',
                'orientation' => 'horizontal_three_part',
                'issue_date' => now(),
                'due_date' => now()->addDays(10), // default due days
                'status' => 'draft',
                'sequence_no' => $voucherData['sequence'],
                'total_amount' => 0.00,
                'balance_amount' => 0.00,
                'generated_by' => filament()->auth()->id() ?? 1,
            ]);

            // 3. Dynamic Fee heads snapshot copying
            $items = [];
            $tuitionTotal = (float) $structure->total_fee;
            $installmentCount = $structure->installment_count ?: 12;
            $firstMonthTuition = $tuitionTotal / $installmentCount;

            $courseId = $student->course_id;

            $admissionHead = \App\Models\FeeHead::where('course_id', $courseId)->where('category', 'admission')->first();
            $admissionFee = (float) ($admissionHead?->default_amount ?: 0.00);

            $endowmentHead = \App\Models\FeeHead::where('course_id', $courseId)->where('category', 'affiliation')->first();
            $enrollmentFee = (float) ($endowmentHead?->default_amount ?: 0.00);

            $verificationHead = \App\Models\FeeHead::where('course_id', $courseId)->where('code', 'like', 'VERIFICATION_%')->first();
            $verificationFee = (float) ($verificationHead?->default_amount ?: 0.00);

            $examHead = \App\Models\FeeHead::where('course_id', $courseId)->where('code', 'like', 'EXAM_%')->first();
            $examinationFee = (float) ($examHead?->default_amount ?: 0.00);

            $hostelHead = \App\Models\FeeHead::where('course_id', $courseId)->where('category', 'hostel')->first();
            $hostelDues = (float) ($hostelHead?->default_amount ?: 0.00);

            $miscHead = \App\Models\FeeHead::where('course_id', $courseId)->where('category', 'miscellaneous')->first();
            $otherMisc = (float) ($miscHead?->default_amount ?: 0.00);

            $feeMappings = [
                'ADMISSION' => ['name' => 'Admission Fee', 'amount' => $admissionFee, 'category' => 'admission'],
                'TUITION_1' => ['name' => 'Tuition Fee / First Installment', 'amount' => $firstMonthTuition, 'category' => 'tuition'],
                'ENROLLMENT' => ['name' => 'Enrollment Fee', 'amount' => $enrollmentFee, 'category' => 'affiliation'],
                'VERIFICATION' => ['name' => 'Verification Fee', 'amount' => $verificationFee, 'category' => 'examination'],
                'EXAM' => ['name' => 'Examination Fee', 'amount' => $examinationFee, 'category' => 'examination'],
                'HOSTEL' => ['name' => 'Hostel Dues', 'amount' => $hostelDues, 'category' => 'hostel'],
                'MISC' => ['name' => 'Miscellaneous / Other', 'amount' => $otherMisc, 'category' => 'miscellaneous'],
            ];

            $order = 1;
            foreach ($feeMappings as $code => $data) {
                if ($data['amount'] > 0) {
                    $head = FeeHead::firstOrCreate(
                        ['code' => $code],
                        [
                            'name' => $data['name'],
                            'category' => $data['category'],
                            'default_amount' => $data['amount'],
                            'applies_to' => 'new_enrollment',
                            'is_discount' => false,
                            'is_refundable' => false,
                            'is_active' => true,
                            'sort_order' => $order++,
                        ]
                    );

                    // Allow staff adjustment if provided
                    $finalAmount = isset($adjustments[$code]) ? (float)$adjustments[$code] : $data['amount'];

                    FeeVoucherItem::create([
                        'fee_voucher_id' => $voucher->id,
                        'fee_head_id' => $head->id,
                        'description' => $head->name,
                        'quantity' => 1,
                        'unit_amount' => $finalAmount,
                        'amount' => $finalAmount,
                        'sort_order' => $head->sort_order,
                    ]);
                }
            }

            // Recalculate totals
            $totals = FeeVoucherCalculator::calculate($voucher);
            $voucher->update($totals);

            // Record audit log
            FeeVoucherAudit::create([
                'fee_voucher_id' => $voucher->id,
                'user_id' => filament()->auth()->id() ?? 1,
                'action' => 'created',
                'new_values' => $voucher->toArray(),
                'ip_address' => request()->ip(),
                'notes' => 'New Enrollment Voucher generated in draft mode.',
            ]);

            return $voucher;
        });
    }

    /**
     * Generate Monthly Installment Voucher.
     */
    public static function generateInstallmentVoucher(Student $student, int $installmentNo, float $tuitionAmount, float $previousBalance = 0.00, float $lateFee = 0.00): FeeVoucher
    {
        return DB::transaction(function () use ($student, $installmentNo, $tuitionAmount, $previousBalance, $lateFee) {
            // Prevent duplicate installment voucher for same student & installment
            $exists = FeeVoucher::where('student_id', $student->id)
                ->where('voucher_type', 'monthly_installment')
                ->where('installment_id', $installmentNo)
                ->whereNotIn('status', ['cancelled', 'void'])
                ->first();

            if ($exists) {
                throw new \Exception("Voucher for Installment #{$installmentNo} already exists for this student.");
            }

            $year = now()->format('Y');
            $campus = $student->campus;
            $voucherData = self::generateVoucherNumber($campus, 'monthly_installment', $year);

            $feeAccount = StudentFeeAccount::where('student_id', $student->id)->first();

            $voucher = FeeVoucher::create([
                'voucher_number' => $voucherData['number'],
                'title' => "Tuition Installment #{$installmentNo}",
                'student_id' => $student->id,
                'admission_id' => $student->admission_id,
                'campus_id' => $student->campus_id,
                'course_id' => $student->course_id,
                'academic_session_id' => $student->admission->academic_session_id ?? null,
                'installment_id' => $installmentNo,
                'student_fee_account_id' => $feeAccount ? $feeAccount->id : null,
                'voucher_type' => 'monthly_installment',
                'orientation' => 'horizontal_three_part',
                'issue_date' => now(),
                'due_date' => now()->addDays(10),
                'status' => 'draft',
                'sequence_no' => $voucherData['sequence'],
                'total_amount' => 0.00,
                'balance_amount' => 0.00,
                'previous_balance' => $previousBalance,
                'late_fee_amount' => $lateFee,
                'generated_by' => filament()->auth()->id() ?? 1,
            ]);

            // Add Tuition Item
            $tuitionHead = FeeHead::firstOrCreate(
                ['code' => 'TUITION_REC'],
                [
                    'name' => 'Monthly Fee / Installment',
                    'category' => 'tuition',
                    'default_amount' => $tuitionAmount,
                    'applies_to' => 'monthly_installment',
                    'is_discount' => false,
                    'is_refundable' => false,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            FeeVoucherItem::create([
                'fee_voucher_id' => $voucher->id,
                'fee_head_id' => $tuitionHead->id,
                'description' => "Tuition Installment #{$installmentNo}",
                'quantity' => 1,
                'unit_amount' => $tuitionAmount,
                'amount' => $tuitionAmount,
                'sort_order' => 1,
            ]);

            // Recalculate totals
            $totals = FeeVoucherCalculator::calculate($voucher);
            $voucher->update($totals);

            // Record audit log
            FeeVoucherAudit::create([
                'fee_voucher_id' => $voucher->id,
                'user_id' => filament()->auth()->id() ?? 1,
                'action' => 'created',
                'new_values' => $voucher->toArray(),
                'ip_address' => request()->ip(),
                'notes' => "Monthly Installment Voucher #{$installmentNo} generated.",
            ]);

            return $voucher;
        });
    }

    /**
     * Issue a Voucher.
     */
    public static function issueVoucher(FeeVoucher $voucher): void
    {
        DB::transaction(function () use ($voucher) {
            if ($voucher->status !== 'draft') {
                throw new \Exception("Only draft vouchers can be issued.");
            }

            $voucher->update([
                'status' => 'issued'
            ]);

            // Audit
            FeeVoucherAudit::create([
                'fee_voucher_id' => $voucher->id,
                'user_id' => filament()->auth()->id() ?? 1,
                'action' => 'issued',
                'new_values' => ['status' => 'issued'],
                'ip_address' => request()->ip(),
                'notes' => 'Voucher issued to student.',
            ]);
        });
    }

    /**
     * Cancel a Voucher.
     */
    public static function cancelVoucher(FeeVoucher $voucher, string $reason): void
    {
        DB::transaction(function () use ($voucher, $reason) {
            if ($voucher->status === 'paid') {
                throw new \Exception("Cannot cancel a paid voucher.");
            }

            $oldStatus = $voucher->status;
            $voucher->update([
                'status' => 'cancelled',
                'cancelled_by' => filament()->auth()->id() ?? 1,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'balance_amount' => 0.00 // clear balance when cancelled
            ]);

            // Recalculate StudentFeeAccount
            if ($voucher->feeAccount) {
                self::recalculateAccountTotals($voucher->feeAccount);
            }

            // Audit
            FeeVoucherAudit::create([
                'fee_voucher_id' => $voucher->id,
                'user_id' => filament()->auth()->id() ?? 1,
                'action' => 'cancelled',
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => 'cancelled', 'cancellation_reason' => $reason],
                'ip_address' => request()->ip(),
                'notes' => 'Voucher cancelled.',
            ]);
        });
    }

    /**
     * Record a Payment.
     */
    public static function recordPayment(FeeVoucher $voucher, array $paymentData): FeePayment
    {
        return DB::transaction(function () use ($voucher, $paymentData) {
            if (in_array($voucher->status, ['paid', 'cancelled', 'void'])) {
                throw new \Exception("Voucher is not in an active payable status.");
            }

            $amount = (float)$paymentData['amount'];
            if ($amount <= 0) {
                throw new \Exception("Payment amount must be greater than zero.");
            }

            if ($amount > (float)$voucher->balance_amount) {
                throw new \Exception("Overpayment is not allowed. Voucher balance is PKR " . number_format($voucher->balance_amount, 2));
            }

            // Create Payment receipt
            $receiptNumber = 'REC-' . strtoupper(str_shuffle(now()->format('ymdHis')));
            $payment = FeePayment::create([
                'student_id' => $voucher->student_id,
                'student_fee_account_id' => $voucher->student_fee_account_id,
                'fee_voucher_id' => $voucher->id,
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'payment_method' => $paymentData['payment_method'] ?? 'cash',
                'transaction_reference' => $paymentData['transaction_reference'] ?? null,
                'bank_name' => $paymentData['bank_name'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'collected_by' => filament()->auth()->id() ?? 1,
                'status' => 'paid',
            ]);

            // Update voucher amounts
            $voucher->paid_amount += $amount;
            $voucher->balance_amount -= $amount;
            $voucher->status = $voucher->balance_amount <= 0 ? 'paid' : 'partially_paid';
            $voucher->save();

            // Update Student Fee Account ledger
            if ($voucher->feeAccount) {
                $voucher->feeAccount->amount_paid += $amount;
                $voucher->feeAccount->balance -= $amount;
                if ($voucher->feeAccount->balance <= 0) {
                    $voucher->feeAccount->status = 'paid';
                }
                $voucher->feeAccount->save();
            }

            // Audit
            FeeVoucherAudit::create([
                'fee_voucher_id' => $voucher->id,
                'user_id' => filament()->auth()->id() ?? 1,
                'action' => 'payment_recorded',
                'new_values' => $payment->toArray(),
                'ip_address' => request()->ip(),
                'notes' => "Collected payment of PKR " . number_format($amount, 2) . ". Receipt: {$receiptNumber}.",
            ]);

            return $payment;
        });
    }

    /**
     * Recalculate StudentFeeAccount totals.
     */
    public static function recalculateAccountTotals(StudentFeeAccount $account): void
    {
        $vouchers = FeeVoucher::where('student_fee_account_id', $account->id)
            ->whereNotIn('status', ['cancelled', 'void'])
            ->get();

        $originalFee = $vouchers->sum('total_amount');
        $amountPaid = FeePayment::where('student_fee_account_id', $account->id)->sum('amount');
        $balance = $originalFee - $amountPaid;

        $account->update([
            'original_fee' => $originalFee,
            'net_payable' => $originalFee - (float)$account->concession_amount,
            'amount_paid' => $amountPaid,
            'balance' => max(0.00, $balance),
            'status' => $balance <= 0 ? 'paid' : 'active',
        ]);
    }
}
