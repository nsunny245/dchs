<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admission_drafts')) {
            Schema::create('admission_drafts', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedTinyInteger('current_step')->default(1);
                $table->unsignedInteger('version')->default(1);
                $table->string('status', 32)->default('draft');
                $table->json('payload');
                $table->timestamp('last_saved_at')->nullable();
                $table->timestamp('finalized_at')->nullable();
                $table->timestamps();
                $table->index(['created_by', 'status', 'last_saved_at']);
            });
        }

        Schema::table('admissions', function (Blueprint $table) {
            if (! Schema::hasColumn('admissions', 'finalization_key')) {
                $table->uuid('finalization_key')->nullable();
            }
            if (! Schema::hasColumn('admissions', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable();
            }
            if (! Schema::hasColumn('admissions', 'finalized_by')) {
                $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('admissions', 'workflow_step')) {
                $table->unsignedTinyInteger('workflow_step')->default(1);
            }
            if (! Schema::hasColumn('admissions', 'completion_percentage')) {
                $table->unsignedTinyInteger('completion_percentage')->default(0);
            }
            if (! Schema::hasColumn('admissions', 'is_document_deficient')) {
                $table->boolean('is_document_deficient')->default(false);
            }
            if (! Schema::hasColumn('admissions', 'concession_value_type')) {
                $table->string('concession_value_type', 16)->default('fixed');
            }
            if (! Schema::hasColumn('admissions', 'concession_value')) {
                $table->decimal('concession_value', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('admissions', 'concession_requested_by')) {
                $table->foreignId('concession_requested_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('admissions', 'concession_approved_by')) {
                $table->foreignId('concession_approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('admissions', 'concession_requested_at')) {
                $table->timestamp('concession_requested_at')->nullable();
            }
            if (! Schema::hasColumn('admissions', 'concession_approved_at')) {
                $table->timestamp('concession_approved_at')->nullable();
            }
            if (! Schema::hasColumn('admissions', 'workflow_metadata')) {
                $table->json('workflow_metadata')->nullable();
            }
        });

        if (! Schema::hasIndex('admissions', 'admissions_finalization_key_unique')) {
            Schema::table('admissions', fn (Blueprint $table) => $table->unique('finalization_key'));
        }

        // Keep a supporting index in place while replacing the legacy unique
        // course index, because the existing course foreign key depends on it.
        // Every operation is guarded because MySQL DDL is not transactional:
        // a shared-host migration can stop after applying only part of this block.
        if (! Schema::hasIndex('fee_structures', 'fee_structures_course_id_index')) {
            Schema::table('fee_structures', function (Blueprint $table) {
                $table->index('course_id', 'fee_structures_course_id_index');
            });
        }

        if (Schema::hasIndex('fee_structures', 'fee_structures_course_id_unique')) {
            Schema::table('fee_structures', function (Blueprint $table) {
                $table->dropUnique('fee_structures_course_id_unique');
            });
        }

        Schema::table('fee_structures', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_structures', 'name')) {
                $table->string('name')->nullable();
            }
            if (! Schema::hasColumn('fee_structures', 'campus_id')) {
                $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('fee_structures', 'academic_session_id')) {
                $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            }
            if (! Schema::hasColumn('fee_structures', 'shift')) {
                $table->string('shift')->nullable();
            }
            if (! Schema::hasColumn('fee_structures', 'version')) {
                $table->unsignedInteger('version')->default(1);
            }
            if (! Schema::hasColumn('fee_structures', 'effective_date')) {
                $table->date('effective_date')->nullable();
            }
            if (! Schema::hasColumn('fee_structures', 'expiry_date')) {
                $table->date('expiry_date')->nullable();
            }
            if (! Schema::hasColumn('fee_structures', 'status')) {
                $table->string('status', 32)->default('active');
            }
            if (! Schema::hasColumn('fee_structures', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('fee_structures', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('fee_structures', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // A default VARCHAR(255) under utf8mb4 can consume 1020 bytes by itself,
        // exceeding the 1000-byte index limit used by some shared-host tables.
        // The workflow only stores compact states such as active and inactive.
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->string('status', 32)->default('active')->change();
        });

        if (! Schema::hasIndex('fee_structures', 'fee_structures_official_plan_lookup')) {
            Schema::table('fee_structures', function (Blueprint $table) {
                $table->index(
                    ['course_id', 'campus_id', 'academic_session_id', 'status', 'effective_date'],
                    'fee_structures_official_plan_lookup',
                );
            });
        }

        Schema::table('student_fee_snapshots', function (Blueprint $table) {
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->unsignedInteger('structure_version')->default(1);
            $table->bigInteger('original_package_paisa')->default(0);
            $table->bigInteger('concession_paisa')->default(0);
            $table->bigInteger('net_payable_paisa')->default(0);
            $table->unsignedSmallInteger('installment_count')->default(1);
            $table->json('installment_schedule')->nullable();
            $table->json('concession_approval')->nullable();
            $table->unique('admission_id');
        });

        Schema::table('concessions', function (Blueprint $table) {
            $table->string('value_type')->default('fixed');
            $table->decimal('value', 12, 2)->default(0);
            $table->string('applies_to')->default('package');
            $table->foreignId('fee_head_id')->nullable()->constrained('fee_heads')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_reference')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('calculation_snapshot')->nullable();
        });

        Schema::create('student_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('installment_number');
            $table->string('title');
            $table->date('due_date');
            $table->bigInteger('gross_paisa');
            $table->bigInteger('concession_paisa')->default(0);
            $table->bigInteger('net_paisa');
            $table->bigInteger('paid_paisa')->default(0);
            $table->string('status')->default('scheduled');
            $table->json('breakdown')->nullable();
            $table->timestamps();
            $table->unique(['student_fee_account_id', 'installment_number'], 'student_installments_account_number_unique');
        });

        Schema::create('student_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('entry_uuid')->unique();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_fee_account_id')->constrained()->cascadeOnDelete();
            $table->string('source_type', 191)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->index(['source_type', 'source_id']);
            $table->string('entry_type');
            $table->bigInteger('debit_paisa')->default(0);
            $table->bigInteger('credit_paisa')->default(0);
            $table->text('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['student_fee_account_id', 'posted_at']);
        });

        Schema::create('admission_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_uuid')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type', 191)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->index(['subject_type', 'subject_id']);
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('student_documents', function (Blueprint $table) {
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_note')->nullable();
            $table->boolean('is_required')->default(false);
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive. Workflow data must be retained.
    }
};
