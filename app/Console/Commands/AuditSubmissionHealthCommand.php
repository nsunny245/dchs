<?php

namespace App\Console\Commands;

use App\Actions\FinalizeAdmissionAction;
use App\Models\Admission;
use App\Models\Student;
use App\Models\User;
use App\Services\Fees\OfficialFeeStructureResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditSubmissionHealthCommand extends Command
{
    protected $signature = 'dgc:audit-submissions
        {--repair : Retry finalization for incomplete admissions}
        {--actor= : Existing user ID recorded as the repair operator}';

    protected $description = 'Audit production schema and report or repair admissions that were saved but not enrolled';

    public function handle(FinalizeAdmissionAction $finalize, OfficialFeeStructureResolver $feeStructures): int
    {
        $requiredTables = [
            'admissions', 'students', 'fee_structures', 'student_fee_accounts',
            'student_fee_snapshots', 'fee_vouchers', 'fee_voucher_items',
            'fee_voucher_audits', 'student_installments', 'student_ledger_entries',
            'admission_audit_logs', 'staff', 'teacher_academics',
            'employment_records', 'staff_documents', 'roles', 'model_has_roles',
        ];
        $missingTables = collect($requiredTables)->reject(fn (string $table) => Schema::hasTable($table))->values();

        $requiredColumns = [
            'admissions' => ['finalization_key', 'finalized_at', 'workflow_step', 'custom_installments', 'custom_installment_interval_months', 'custom_installment_start_date'],
            'staff' => ['employee_id', 'full_name', 'record_status', 'joining_date', 'is_active'],
            'student_fee_snapshots' => ['admission_id', 'installment_schedule'],
            'fee_vouchers' => ['admission_id', 'student_fee_account_id', 'generated_by'],
        ];
        $missingColumns = collect($requiredColumns)->flatMap(fn (array $columns, string $table) => collect($columns)
            ->reject(fn (string $column) => Schema::hasTable($table) && Schema::hasColumn($table, $column))
            ->map(fn (string $column) => "{$table}.{$column}")
        )->values();

        $this->components->info('DGC submission health audit');
        $this->line('Missing tables: '.($missingTables->isEmpty() ? 'none' : $missingTables->join(', ')));
        $this->line('Missing columns: '.($missingColumns->isEmpty() ? 'none' : $missingColumns->join(', ')));

        if ($missingTables->isNotEmpty() || $missingColumns->isNotEmpty()) {
            $this->components->error('The live schema is incomplete. Run php artisan migrate --force before attempting repairs.');

            return self::FAILURE;
        }

        $incomplete = Admission::withoutGlobalScopes()
            ->whereDoesntHave('student')
            ->orderBy('id')
            ->get();
        $orphanStudents = Student::withoutGlobalScopes()
            ->whereDoesntHave('admission')
            ->count();

        $this->line("Admissions saved without a student: {$incomplete->count()}");
        $this->line("Students without an admission: {$orphanStudents}");

        if ($incomplete->isNotEmpty()) {
            $this->table(
                ['Admission ID', 'Applicant', 'CNIC', 'Campus', 'Course', 'Session', 'Fee plan', 'Status'],
                $incomplete->map(fn (Admission $admission) => [
                    $admission->id,
                    $admission->applicant_name,
                    $admission->cnic,
                    $admission->campus_id ?: 'missing',
                    $admission->course_id ?: 'missing',
                    $admission->academic_session_id ?: 'missing',
                    $admission->course_id && $feeStructures->resolve(
                        $admission->course_id,
                        $admission->campus_id,
                        $admission->academic_session_id,
                        $admission->admission_date,
                    ) ? 'available' : 'missing',
                    $admission->status,
                ])->all(),
            );
        }

        if (! $this->option('repair')) {
            $this->newLine();
            $this->comment('Audit only. No records were changed. Add --repair --actor=USER_ID to retry incomplete admissions.');

            return self::SUCCESS;
        }

        $actorId = filter_var($this->option('actor'), FILTER_VALIDATE_INT) ?: null;
        if (! $actorId || ! User::query()->whereKey($actorId)->exists()) {
            $this->components->error('--actor must be the ID of an existing administrator. No repairs were attempted.');

            return self::FAILURE;
        }

        $repaired = 0;
        $failed = 0;
        foreach ($incomplete as $admission) {
            try {
                $student = $finalize->execute($admission, $actorId);
                $this->info("Repaired admission {$admission->id} as {$student->enrollment_number}.");
                $repaired++;
            } catch (Throwable $exception) {
                report($exception);
                $this->error("Admission {$admission->id} failed: {$exception->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->line("Repair result: {$repaired} completed, {$failed} failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
