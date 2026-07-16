<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Add franchisor_id relations to admissions and students with safety checks (plain index columns)
        if (!Schema::hasColumn('admissions', 'franchisor_id')) {
            Schema::table('admissions', function (Blueprint $table) {
                $table->unsignedBigInteger('franchisor_id')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('students', 'franchisor_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('franchisor_id')->nullable()->index();
            });
        }

        // 2. Create franchisor_student_payments table if not exists (using plain index columns to avoid cPanel engine conflicts)
        if (!Schema::hasTable('franchisor_student_payments')) {
            Schema::create('franchisor_student_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('franchisor_id')->index();
                $table->unsignedBigInteger('admission_id')->nullable()->index();
                $table->decimal('total_amount', 12, 2);
                $table->decimal('paid_amount', 12, 2)->default(0.00);
                $table->string('status')->default('unpaid');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 3. Create franchisor_payment_installments table if not exists (using plain index columns to avoid cPanel engine conflicts)
        if (!Schema::hasTable('franchisor_payment_installments')) {
            Schema::create('franchisor_payment_installments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('franchisor_student_payment_id')->index();
                $table->string('title');
                $table->decimal('amount', 12, 2);
                $table->date('due_date')->nullable();
                $table->string('status')->default('unpaid');
                $table->date('paid_date')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('transaction_id')->nullable();
                $table->string('receipt_path')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('franchisor_payment_installments');
        Schema::dropIfExists('franchisor_student_payments');

        if (Schema::hasColumn('students', 'franchisor_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('franchisor_id');
            });
        }

        if (Schema::hasColumn('admissions', 'franchisor_id')) {
            Schema::table('admissions', function (Blueprint $table) {
                $table->dropColumn('franchisor_id');
            });
        }
    }
};
