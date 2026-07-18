<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Fee Components Table
        Schema::create('fee_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_structure_id')->index();
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->string('due_rule')->nullable(); // e.g., 'at_admission', 'installments'
            $table->boolean('is_refundable')->default(false);
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
        });

        // 2. Installment Templates Table
        Schema::create('installment_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_structure_id')->index();
            $table->string('type'); // monthly, quarterly, semester-wise, custom, one-time
            $table->integer('installments_count');
            $table->timestamps();
        });

        // 3. Student Fee Accounts Table
        Schema::create('student_fee_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('admission_id')->index();
            $table->decimal('original_fee', 10, 2);
            $table->decimal('concession_amount', 10, 2)->default(0.00);
            $table->decimal('net_payable', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2);
            $table->string('status')->default('active'); // active, paid, overdue, closed
            $table->timestamps();
        });

        // 4. Student Fee Snapshots Table
        Schema::create('student_fee_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('fee_structure_id')->index();
            $table->json('fee_structure_data');
            $table->timestamps();
        });

        // 5. Student Vouchers Table
        Schema::create('student_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('student_fee_account_id')->index();
            $table->string('voucher_number')->unique();
            $table->string('title');
            $table->date('due_date');
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2);
            $table->string('status')->default('upcoming'); // upcoming, due, partially_paid, paid, overdue, waived, cancelled, reversed
            $table->integer('sequence_no');
            $table->text('waived_reason')->nullable();
            $table->unsignedBigInteger('waived_by')->nullable();
            $table->text('reversed_reason')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->timestamps();
        });

        // 6. Payments Table (Replaces basic payments)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('student_fee_account_id')->index();
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('payment_method');
            $table->string('transaction_reference')->nullable();
            $table->string('bank_account')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('collected_by')->index();
            $table->timestamps();
        });

        // 7. Payment Allocations Table
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id')->index();
            $table->unsignedBigInteger('student_voucher_id')->index();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        // 8. Concessions Table
        Schema::create('concessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('concession_type');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->string('approving_authority')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('supporting_document')->nullable();
            $table->timestamps();
        });

        // 9. Student Documents Table
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('document_type'); // cnic, matric, inter, domicile, photo, etc.
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending'); // uploaded, pending, not_required, verified, rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. Rebuild Audit Logs Table
        Schema::create('rebuild_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action');
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_components');
        Schema::dropIfExists('installment_templates');
        Schema::dropIfExists('student_fee_accounts');
        Schema::dropIfExists('student_fee_snapshots');
        Schema::dropIfExists('student_vouchers');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('concessions');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('rebuild_audit_logs');
    }
};
