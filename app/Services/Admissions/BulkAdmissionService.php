<?php

namespace App\Services\Admissions;

use App\Models\Admission;
use App\Models\AdmissionAuditLog;
use App\Models\Concession;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentFeeAccount;
use App\Models\StudentFeeSnapshot;
use App\Models\StudentImportBatch;
use App\Models\StudentImportRow;
use App\Models\StudentLedgerEntry;
use App\Models\User;
use App\Services\Fees\InstallmentPlanGenerator;
use App\Services\Fees\OfficialFeeStructureResolver;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Throwable;

class BulkAdmissionService
{
    public function __construct(
        private readonly OfficialFeeStructureResolver $feeStructures,
        private readonly EnrollmentNumberService $enrollmentNumbers,
        private readonly InstallmentPlanGenerator $money,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $defaults
     */
    public function import(array $rows, User $actor, array $defaults): StudentImportBatch
    {
        $campusId = (int) ($defaults['campus_id'] ?? $actor->campus_id);
        $this->authorizeCampus($actor, $campusId);

        $batch = StudentImportBatch::create([
            'uuid' => (string) Str::uuid(),
            'campus_id' => $campusId,
            'created_by' => $actor->id,
            'status' => 'processing',
            'total_rows' => count($rows),
            'defaults' => $defaults,
            'started_at' => now(),
        ]);

        foreach (array_values($rows) as $index => $rawRow) {
            $importRow = $batch->rows()->create([
                'row_number' => $index + 1,
                'idempotency_key' => (string) Str::uuid(),
                'raw_data' => $rawRow,
            ]);

            try {
                DB::transaction(fn () => $this->importRow($importRow, $rawRow, $defaults, $actor));
                $batch->increment('successful_rows');
            } catch (Throwable $exception) {
                report($exception);
                $messages = $exception instanceof ValidationException
                    ? Arr::flatten($exception->errors())
                    : ['This row could not be imported. Review its details and try again.'];

                $importRow->update([
                    'status' => 'failed',
                    'errors' => array_values($messages),
                    'processed_at' => now(),
                ]);
                $batch->increment('failed_rows');
            }
        }

        $batch->refresh()->update([
            'status' => $batch->failed_rows > 0
                ? ($batch->successful_rows > 0 ? 'completed_with_errors' : 'failed')
                : 'completed',
            'completed_at' => now(),
        ]);

        return $batch->fresh('rows');
    }

    /** @param array<string, mixed> $rawRow @param array<string, mixed> $defaults */
    private function importRow(StudentImportRow $importRow, array $rawRow, array $defaults, User $actor): void
    {
        $data = array_merge($defaults, $rawRow);
        $data['campus_id'] = (int) ($data['campus_id'] ?? 0);
        $data['course_id'] = (int) ($data['course_id'] ?? 0);
        $data['academic_session_id'] = (int) ($data['academic_session_id'] ?? 0);
        $data['cnic'] = $this->normalizeCnic((string) ($data['cnic'] ?? ''));
        $data['phone'] = trim((string) ($data['phone'] ?? ''));
        $data['concession_amount'] = round(max(0, (float) ($data['concession_amount'] ?? 0)), 2);
        $data['amount_paid'] = round(max(0, (float) ($data['amount_paid'] ?? 0)), 2);
        $data['admission_date'] = $data['admission_date'] ?? now()->toDateString();

        $this->authorizeCampus($actor, $data['campus_id']);

        Validator::make($data, [
            'applicant_name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'phone' => ['required', 'string', 'max:30'],
            'cnic' => [
                'required', 'string', 'max:20',
                Rule::unique('admissions', 'cnic'),
            ],
            'address' => ['required', 'string', 'max:2000'],
            'previous_education' => ['required', 'string', 'max:255'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'concession_amount' => ['numeric', 'min:0'],
            'amount_paid' => ['numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', Rule::in(['cash', 'bank_transfer', 'cheque', 'card', 'other'])],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'existing_enrollment_number' => ['nullable', 'string', 'max:100', Rule::unique('students', 'enrollment_number')],
        ], [], [
            'applicant_name' => 'student name',
            'previous_education' => 'qualification',
            'academic_session_id' => 'session',
            'course_id' => 'program',
        ])->validate();

        $sessionBelongsToCampus = DB::table('academic_sessions')
            ->where('id', $data['academic_session_id'])
            ->where('campus_id', $data['campus_id'])
            ->exists();
        if (! $sessionBelongsToCampus) {
            throw ValidationException::withMessages(['academic_session_id' => 'The selected session does not belong to this campus.']);
        }

        $structure = $this->feeStructures->resolve(
            $data['course_id'],
            $data['campus_id'],
            $data['academic_session_id'],
            $data['admission_date'],
        );
        if (! $structure) {
            throw ValidationException::withMessages(['course_id' => 'No active fee plan is available for this campus, session, and program.']);
        }

        $tuition = round(max(0, (float) $structure->total_fee), 2);
        $concession = min($data['concession_amount'], $tuition);
        $netTuition = round($tuition - $concession, 2);
        if ($data['amount_paid'] > $netTuition) {
            throw ValidationException::withMessages(['amount_paid' => 'Opening paid amount cannot exceed net tuition.']);
        }
        $outstanding = round($netTuition - $data['amount_paid'], 2);

        $campus = $structure->campus_id
            ? $structure->campus()->firstOrFail()
            : \App\Models\Campus::query()->findOrFail($data['campus_id']);
        if ((int) $campus->id !== $data['campus_id']) {
            $campus = \App\Models\Campus::query()->findOrFail($data['campus_id']);
        }
        $course = \App\Models\Course::query()->findOrFail($data['course_id']);
        $enrollmentNumber = filled($data['existing_enrollment_number'] ?? null)
            ? trim((string) $data['existing_enrollment_number'])
            : $this->enrollmentNumbers->generate($campus, $course);

        $workflowMetadata = [
            'source' => 'manual_bulk_admission',
            'profile_complete' => false,
            'import_batch_uuid' => $importRow->batch->uuid,
            'opening_balance' => [
                'tuition' => $tuition,
                'concession' => $concession,
                'paid' => $data['amount_paid'],
                'outstanding' => $outstanding,
            ],
        ];

        $admission = Admission::create([
            'applicant_name' => trim($data['applicant_name']),
            'father_name' => trim($data['father_name']),
            'cnic' => $data['cnic'],
            'dob' => $data['dob'],
            'gender' => $data['gender'],
            'phone' => $data['phone'],
            'address' => trim($data['address']),
            'previous_education' => trim($data['previous_education']),
            'academic_details' => [[
                'level' => 'other',
                'degree_title' => trim($data['previous_education']),
            ]],
            'course_id' => $data['course_id'],
            'campus_id' => $data['campus_id'],
            'academic_session_id' => $data['academic_session_id'],
            'shift' => $data['shift'] ?? 'morning',
            'admission_date' => $data['admission_date'],
            'custom_tuition_fee' => $tuition,
            'concession_amount' => $concession,
            'concession_value' => $concession,
            'concession_status' => $concession > 0 ? 'approved' : 'not_requested',
            'concession_reason' => $data['concession_reason'] ?? 'Historical opening balance import',
            'concession_approved_by' => $concession > 0 ? $actor->id : null,
            'concession_approved_at' => $concession > 0 ? now() : null,
            'status' => 'enrolled',
            'enrollment_no' => $enrollmentNumber,
            'workflow_step' => 1,
            'completion_percentage' => 25,
            'is_document_deficient' => true,
            'finalization_key' => (string) Str::uuid(),
            'finalized_at' => now(),
            'finalized_by' => $actor->id,
            'workflow_metadata' => $workflowMetadata,
        ]);

        $email = 'bulk.'.Str::lower(Str::random(20)).'@student.dgc.invalid';
        $user = User::create([
            'name' => $admission->applicant_name,
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'phone' => $admission->phone,
            'campus_id' => $admission->campus_id,
            'status' => false,
        ]);
        try {
            $role = Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);
            $user->roles()->syncWithoutDetaching([$role->id]);
        } catch (Throwable) {
            // Role provisioning must not invalidate an otherwise valid historical record.
        }

        $student = Student::create([
            'user_id' => $user->id,
            'enrollment_number' => $enrollmentNumber,
            'full_name' => $admission->applicant_name,
            'campus_id' => $admission->campus_id,
            'course_id' => $admission->course_id,
            'batch_year' => now()->year,
            'status' => 'active',
            'admission_id' => $admission->id,
            'enrollment_date' => $data['admission_date'],
            'is_active' => true,
            'profile_complete' => false,
            'student_import_batch_id' => $importRow->student_import_batch_id,
            'imported_at' => now(),
        ]);

        $account = StudentFeeAccount::create([
            'student_id' => $student->id,
            'admission_id' => $admission->id,
            'original_fee' => $tuition,
            'concession_amount' => $concession,
            'net_payable' => $netTuition,
            'amount_paid' => $data['amount_paid'],
            'opening_paid_amount' => $data['amount_paid'],
            'opening_balance_recorded_at' => $data['amount_paid'] > 0 ? now() : null,
            'balance' => $outstanding,
            'status' => $outstanding <= 0 ? 'paid' : 'active',
            'source' => 'bulk_import',
        ]);

        StudentFeeSnapshot::create([
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'fee_structure_data' => $structure->toArray(),
            'admission_id' => $admission->id,
            'campus_id' => $admission->campus_id,
            'academic_session_id' => $admission->academic_session_id,
            'structure_version' => $structure->version ?: 1,
            'original_package_paisa' => $this->money->toPaisa($tuition),
            'concession_paisa' => $this->money->toPaisa($concession),
            'net_payable_paisa' => $this->money->toPaisa($netTuition),
            'installment_count' => 0,
            'installment_schedule' => [],
            'concession_approval' => ['status' => $concession > 0 ? 'approved' : 'not_requested', 'approved_by' => $actor->id],
        ]);

        if ($concession > 0) {
            Concession::create([
                'admission_id' => $admission->id,
                'student_id' => $student->id,
                'concession_type' => 'special',
                'value_type' => 'fixed',
                'value' => $concession,
                'amount' => $concession,
                'reason' => $admission->concession_reason,
                'status' => 'approved',
                'requested_by' => $actor->id,
                'approved_by' => $actor->id,
                'requested_at' => now(),
                'decided_at' => now(),
                'calculation_snapshot' => ['source' => 'manual_bulk_admission', 'amount' => $concession],
            ]);
        }

        StudentLedgerEntry::create([
            'entry_uuid' => 'bulk-assessment-'.$importRow->idempotency_key,
            'student_id' => $student->id,
            'student_fee_account_id' => $account->id,
            'source_type' => StudentImportRow::class,
            'source_id' => $importRow->id,
            'entry_type' => 'fee_assessment',
            'debit_paisa' => $this->money->toPaisa($tuition),
            'description' => 'Historical opening tuition assessment',
            'created_by' => $actor->id,
            'posted_at' => $data['payment_date'] ?? now(),
            'metadata' => ['source' => 'manual_bulk_admission'],
        ]);
        if ($concession > 0) {
            StudentLedgerEntry::create([
                'entry_uuid' => 'bulk-concession-'.$importRow->idempotency_key,
                'student_id' => $student->id,
                'student_fee_account_id' => $account->id,
                'source_type' => StudentImportRow::class,
                'source_id' => $importRow->id,
                'entry_type' => 'concession',
                'credit_paisa' => $this->money->toPaisa($concession),
                'description' => 'Historical opening concession',
                'created_by' => $actor->id,
                'posted_at' => $data['payment_date'] ?? now(),
                'metadata' => ['source' => 'manual_bulk_admission'],
            ]);
        }

        if ($data['amount_paid'] > 0) {
            $payment = FeePayment::create([
                'student_id' => $student->id,
                'student_fee_account_id' => $account->id,
                'fee_voucher_id' => null,
                'receipt_number' => 'OPEN-'.strtoupper(substr(str_replace('-', '', $importRow->idempotency_key), 0, 16)),
                'amount' => $data['amount_paid'],
                'status' => 'paid',
                'payment_date' => $data['payment_date'] ?? $data['admission_date'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'notes' => 'Historical opening payment recorded during manual bulk admission.',
                'collected_by' => $actor->id,
                'metadata' => ['source' => 'manual_bulk_admission', 'unallocated_opening_payment' => true],
            ]);
            StudentLedgerEntry::create([
                'entry_uuid' => 'bulk-payment-'.$importRow->idempotency_key,
                'student_id' => $student->id,
                'student_fee_account_id' => $account->id,
                'source_type' => FeePayment::class,
                'source_id' => $payment->id,
                'entry_type' => 'payment',
                'credit_paisa' => $this->money->toPaisa($data['amount_paid']),
                'description' => 'Historical opening payment',
                'created_by' => $actor->id,
                'posted_at' => $data['payment_date'] ?? now(),
                'metadata' => ['source' => 'manual_bulk_admission'],
            ]);
        }

        $normalized = array_merge($data, [
            'official_tuition' => $tuition,
            'net_tuition' => $netTuition,
            'outstanding' => $outstanding,
            'fee_structure_id' => $structure->id,
            'enrollment_number' => $enrollmentNumber,
        ]);
        $importRow->update([
            'status' => 'imported',
            'normalized_data' => $normalized,
            'admission_id' => $admission->id,
            'student_id' => $student->id,
            'processed_at' => now(),
        ]);

        AdmissionAuditLog::create([
            'request_uuid' => $importRow->idempotency_key,
            'user_id' => $actor->id,
            'campus_id' => $admission->campus_id,
            'subject_type' => Admission::class,
            'subject_id' => $admission->id,
            'action' => 'manual_bulk_admission_imported',
            'new_values' => [
                'student_id' => $student->id,
                'fee_account_id' => $account->id,
                'opening_paid_amount' => $data['amount_paid'],
                'outstanding' => $outstanding,
                'installments_generated' => false,
            ],
            'ip_address' => request()->ip(),
            'notes' => 'Basic student profile imported. Full profile and installment schedule remain pending.',
        ]);
    }

    private function authorizeCampus(User $actor, int $campusId): void
    {
        if ($campusId < 1 || (! $actor->hasRole('Super Admin') && (int) $actor->campus_id !== $campusId)) {
            throw ValidationException::withMessages(['campus_id' => 'You may only import students into your assigned campus.']);
        }
    }

    private function normalizeCnic(string $cnic): string
    {
        $digits = preg_replace('/\D+/', '', $cnic);

        return strlen($digits) === 13
            ? substr($digits, 0, 5).'-'.substr($digits, 5, 7).'-'.substr($digits, 12)
            : trim($cnic);
    }
}
