<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::table('students', function (Blueprint $table): void {
            $table->boolean('profile_complete')->default(true)->after('is_active');
            $table->foreignId('student_import_batch_id')->nullable()->after('profile_complete')
                ->constrained()->nullOnDelete();
            $table->timestamp('imported_at')->nullable()->after('student_import_batch_id');
        });

        Schema::table('student_fee_accounts', function (Blueprint $table): void {
            $table->decimal('opening_paid_amount', 14, 2)->default(0)->after('amount_paid');
            $table->timestamp('opening_balance_recorded_at')->nullable()->after('opening_paid_amount');
            $table->string('source', 32)->default('admission')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('student_fee_accounts', function (Blueprint $table): void {
            $table->dropColumn(['opening_paid_amount', 'opening_balance_recorded_at', 'source']);
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('student_import_batch_id');
            $table->dropColumn(['profile_complete', 'imported_at']);
        });

        Schema::dropIfExists('student_import_rows');
        Schema::dropIfExists('student_import_batches');
    }
};
