<?php

namespace App\Actions;

use App\Models\Admission;
use App\Models\AdmissionDraft;
use App\Models\Concession;
use App\Models\FeeVoucher;
use App\Models\Student;
use App\Models\StudentFeeAccount;
use App\Models\StudentFeeSnapshot;
use App\Models\StudentInstallment;
use App\Models\StudentLedgerEntry;
use App\Services\Admissions\AdmissionAuditService;
use App\Services\EnrollmentService;
use App\Services\Fees\InstallmentPlanGenerator;
use App\Services\Fees\VoucherGenerationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FinalizeAdmissionAction
{
    public function __construct(
        private readonly InstallmentPlanGenerator $installments,
        private readonly VoucherGenerationService $vouchers,
        private readonly AdmissionAuditService $audit,
    ) {}

    public function execute(Admission $admission, ?int $actorId = null, ?AdmissionDraft $draft = null): Student
    {
        return DB::transaction(function () use ($admission, $actorId, $draft) {
            $admission = Admission::query()->lockForUpdate()->findOrFail($admission->getKey());

            if ($student = Student::where('admission_id', $admission->id)->first()) {
                return $student;
            }

            if (! $admission->campus_id || ! $admission->course_id || ! $admission->academic_session_id) {
                throw ValidationException::withMessages(['course_id' => 'Campus, session, and course are required.']);
            }

            if ((float) $admission->concession_amount > 0 && $admission->concession_status !== 'approved') {
                throw ValidationException::withMessages(['concession_amount' => 'The concession must be approved before finalization.']);
            }

            if ((float) $admission->concession_amount > 0) {
                Concession::updateOrCreate(
                    ['admission_id' => $admission->id, 'status' => 'approved'],
                    [
                        'student_id' => null,
                        'concession_type' => $admission->concession_type ?: 'special',
                        'value_type' => 'fixed',
                        'value' => $admission->concession_amount,
                        'amount' => $admission->concession_amount,
                        'reason' => $admission->concession_reason,
                        'approving_authority' => $admission->concession_approver,
                        'requested_by' => $admission->concession_requested_by ?: $actorId,
                        'approved_by' => $admission->concession_approved_by ?: $actorId,
                        'approval_reference' => data_get($admission->workflow_metadata, 'concession_approval_reference'),
                        'requested_at' => $admission->concession_requested_at ?: now(),
                        'decided_at' => $admission->concession_approved_at ?: now(),
                        'calculation_snapshot' => ['amount' => (string) $admission->concession_amount],
                    ],
                );
            }

            $admission->forceFill(['finalization_key' => $admission->finalization_key ?: (string) Str::uuid()])->save();
            $student = EnrollmentService::enroll($admission, $actorId);
            Concession::where('admission_id', $admission->id)
                ->where('status', 'approved')
                ->update(['student_id' => $student->id]);
            $account = StudentFeeAccount::where('student_id', $student->id)->firstOrFail();

            $generatedVouchers = FeeVoucher::where('student_id', $student->id)
                ->orderBy('sequence_no')
                ->get()
                ->values();
            $schedule = $this->installments->normalize(
                $generatedVouchers->map(fn (FeeVoucher $voucher) => [
                    'title' => $voucher->title,
                    'due_date' => $voucher->due_date->toDateString(),
                    'amount' => $voucher->total_amount,
                ])->all(),
                (string) $account->net_payable,
            );

            foreach ($schedule as $row) {
                $installment = StudentInstallment::firstOrCreate(
                    ['student_fee_account_id' => $account->id, 'installment_number' => $row['number']],
                    [
                        'admission_id' => $admission->id,
                        'student_id' => $student->id,
                        'title' => $row['title'],
                        'due_date' => $row['due_date'],
                        'gross_paisa' => $row['gross_paisa'],
                        'concession_paisa' => $row['concession_paisa'],
                        'net_paisa' => $row['net_paisa'],
                        'status' => 'scheduled',
                    ],
                );

                $this->vouchers->linkToInstallment($generatedVouchers, $installment, $row);
            }

            StudentFeeSnapshot::where('student_id', $student->id)->update([
                'original_package_paisa' => $this->installments->toPaisa($account->original_fee),
                'concession_paisa' => $this->installments->toPaisa($account->concession_amount),
                'net_payable_paisa' => $this->installments->toPaisa($account->net_payable),
                'installment_count' => count($schedule),
                'installment_schedule' => json_encode($schedule),
                'concession_approval' => json_encode([
                    'status' => $admission->concession_status,
                    'approved_by' => $admission->concession_approved_by ?: $actorId,
                    'approved_at' => optional($admission->concession_approved_at)->toIso8601String(),
                    'reference' => data_get($admission->workflow_metadata, 'concession_approval_reference'),
                ]),
            ]);

            StudentLedgerEntry::firstOrCreate(
                ['entry_uuid' => "admission-fees-{$admission->id}"],
                [
                    'student_id' => $student->id,
                    'student_fee_account_id' => $account->id,
                    'source_type' => Admission::class,
                    'source_id' => $admission->id,
                    'entry_type' => 'fee_assessment',
                    'debit_paisa' => $this->installments->toPaisa($account->original_fee),
                    'description' => 'Opening fee assessment',
                    'created_by' => $actorId,
                    'posted_at' => now(),
                ],
            );

            if ((float) $account->concession_amount > 0) {
                StudentLedgerEntry::firstOrCreate(
                    ['entry_uuid' => "admission-concession-{$admission->id}"],
                    [
                        'student_id' => $student->id,
                        'student_fee_account_id' => $account->id,
                        'source_type' => Concession::class,
                        'source_id' => Concession::where('admission_id', $admission->id)->value('id'),
                        'entry_type' => 'concession',
                        'credit_paisa' => $this->installments->toPaisa($account->concession_amount),
                        'description' => 'Approved admission concession',
                        'created_by' => $actorId,
                        'posted_at' => now(),
                    ],
                );
            }

            $admission->update([
                'finalized_at' => now(),
                'finalized_by' => $actorId,
                'workflow_step' => 7,
                'completion_percentage' => 100,
                'is_document_deficient' => empty($admission->cnic_copy)
                    || empty($admission->matric_copy)
                    || empty($admission->domicile_copy),
            ]);

            if ($draft) {
                $draft->update(['status' => 'finalized', 'admission_id' => $admission->id, 'finalized_at' => now()]);
            }

            $this->audit->record($admission, 'admission_finalized', [], [
                'student_id' => $student->id,
                'installments' => count($schedule),
                'vouchers' => FeeVoucher::where('student_id', $student->id)->count(),
            ]);

            return $student;
        });
    }
}
