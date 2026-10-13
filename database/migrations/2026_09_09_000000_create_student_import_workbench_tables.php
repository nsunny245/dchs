<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_import_batches')) {
            Schema::create('student_import_batches', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('processing');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('defaults')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['campus_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('student_import_rows')) {
            Schema::create('student_import_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_import_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->uuid('idempotency_key')->unique();
            $table->string('status', 32)->default('pending');
            $table->json('raw_data');
            $table->json('normalized_data')->nullable();
            $table->json('errors')->nullable();
            $table->json('warnings')->nullable();
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['student_import_batch_id', 'row_number'], 'student_import_rows_batch_row_unique');
            });
        }

        if (! Schema::hasColumn('students', 'profile_complete')) {
            Schema::table('students', function (Blueprint $table): void {
                $table->boolean('profile_complete')->default(true)->after('is_active');
            });
        }
        if (! Schema::hasColumn('students', 'student_import_batch_id')) {
            Schema::table('students', function (Blueprint $table): void {
                // Kept unconstrained for compatibility with legacy MyISAM and
                // installations whose existing primary keys are not BIGINT.
                $table->unsignedBigInteger('student_import_batch_id')->nullable()->after('profile_complete');
                $table->index('student_import_batch_id', 'students_import_batch_index');
            });
        }
        if (! Schema::hasColumn('students', 'imported_at')) {
            Schema::table('students', function (Blueprint $table): void {
                $table->timestamp('imported_at')->nullable()->after('student_import_batch_id');
            });
        }

        if (! Schema::hasColumn('student_fee_accounts', 'opening_paid_amount')) {
            Schema::table('student_fee_accounts', function (Blueprint $table): void {
                $table->decimal('opening_paid_amount', 14, 2)->default(0)->after('amount_paid');
            });
        }
        if (! Schema::hasColumn('student_fee_accounts', 'opening_balance_recorded_at')) {
            Schema::table('student_fee_accounts', function (Blueprint $table): void {
                $table->timestamp('opening_balance_recorded_at')->nullable()->after('opening_paid_amount');
            });
        }
        if (! Schema::hasColumn('student_fee_accounts', 'source')) {
            Schema::table('student_fee_accounts', function (Blueprint $table): void {
                $table->string('source', 32)->default('admission')->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('student_fee_accounts', function (Blueprint $table): void {
            $table->dropColumn(['opening_paid_amount', 'opening_balance_recorded_at', 'source']);
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->dropIndex('students_import_batch_index');
            $table->dropColumn(['student_import_batch_id', 'profile_complete', 'imported_at']);
        });

        Schema::dropIfExists('student_import_rows');
        Schema::dropIfExists('student_import_batches');
    }
};
