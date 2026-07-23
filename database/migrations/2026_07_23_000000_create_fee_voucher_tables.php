<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Drop the legacy unused fee_payments table if it exists
        Schema::dropIfExists('fee_payments');

        // 2. Rename payments table to fee_payments
        if (Schema::hasTable('payments') && !Schema::hasTable('fee_payments')) {
            Schema::rename('payments', 'fee_payments');
        }

        // 3. Alter fee_payments table to align with FeePayment model
        Schema::table('fee_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_payments', 'fee_voucher_id')) {
                $table->unsignedBigInteger('fee_voucher_id')->nullable()->index()->after('student_fee_account_id');
            }
            if (!Schema::hasColumn('fee_payments', 'status')) {
                $table->string('status')->default('paid')->after('amount');
            }
            if (!Schema::hasColumn('fee_payments', 'metadata')) {
                $table->json('metadata')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('fee_payments', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('payment_method');
            }
        });

        // 4. Rename student_vouchers table to fee_vouchers
        if (Schema::hasTable('student_vouchers') && !Schema::hasTable('fee_vouchers')) {
            Schema::rename('student_vouchers', 'fee_vouchers');
        }

        // 5. Alter fee_vouchers table to add missing schema fields
        Schema::table('fee_vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_vouchers', 'uuid')) {
                $table->string('uuid', 36)->nullable()->after('id');
            }
            if (!Schema::hasColumn('fee_vouchers', 'admission_id')) {
                $table->unsignedBigInteger('admission_id')->nullable()->index()->after('student_id');
            }
            if (!Schema::hasColumn('fee_vouchers', 'campus_id')) {
                $table->unsignedBigInteger('campus_id')->nullable()->index()->after('admission_id');
            }
            if (!Schema::hasColumn('fee_vouchers', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->index()->after('campus_id');
            }
            if (!Schema::hasColumn('fee_vouchers', 'academic_session_id')) {
                $table->unsignedBigInteger('academic_session_id')->nullable()->index()->after('course_id');
            }
            if (!Schema::hasColumn('fee_vouchers', 'fee_structure_id')) {
                $table->unsignedBigInteger('fee_structure_id')->nullable()->index()->after('academic_session_id');
            }
            if (!Schema::hasColumn('fee_vouchers', 'installment_id')) {
                $table->unsignedBigInteger('installment_id')->nullable()->index()->after('fee_structure_id');
            }
            if (!Schema::hasColumn('fee_vouchers', 'voucher_type')) {
                $table->string('voucher_type')->default('monthly_installment')->after('installment_id');
            }
            if (!Schema::hasColumn('fee_vouchers', 'orientation')) {
                $table->string('orientation')->default('horizontal_three_part')->after('voucher_type');
            }
            if (!Schema::hasColumn('fee_vouchers', 'issue_date')) {
                $table->date('issue_date')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('fee_vouchers', 'subtotal')) {
                $table->decimal('subtotal', 14, 2)->default(0.00)->after('due_date');
            }
            if (!Schema::hasColumn('fee_vouchers', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0.00)->after('subtotal');
            }
            if (!Schema::hasColumn('fee_vouchers', 'scholarship_amount')) {
                $table->decimal('scholarship_amount', 14, 2)->default(0.00)->after('discount_amount');
            }
            if (!Schema::hasColumn('fee_vouchers', 'fine_amount')) {
                $table->decimal('fine_amount', 14, 2)->default(0.00)->after('scholarship_amount');
            }
            if (!Schema::hasColumn('fee_vouchers', 'late_fee_amount')) {
                $table->decimal('late_fee_amount', 14, 2)->default(0.00)->after('fine_amount');
            }
            if (!Schema::hasColumn('fee_vouchers', 'previous_balance')) {
                $table->decimal('previous_balance', 14, 2)->default(0.00)->after('late_fee_amount');
            }
            
            // Rename columns safely if they exist
            if (Schema::hasColumn('fee_vouchers', 'amount') && !Schema::hasColumn('fee_vouchers', 'total_amount')) {
                $table->renameColumn('amount', 'total_amount');
            }
            if (Schema::hasColumn('fee_vouchers', 'balance') && !Schema::hasColumn('fee_vouchers', 'balance_amount')) {
                $table->renameColumn('balance', 'balance_amount');
            }

            if (!Schema::hasColumn('fee_vouchers', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (!Schema::hasColumn('fee_vouchers', 'generated_by')) {
                $table->unsignedBigInteger('generated_by')->nullable()->index()->after('notes');
            }
            if (!Schema::hasColumn('fee_vouchers', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->index()->after('generated_by');
            }
            if (!Schema::hasColumn('fee_vouchers', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('fee_vouchers', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->index()->after('approved_at');
            }
            if (!Schema::hasColumn('fee_vouchers', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('fee_vouchers', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('fee_vouchers', 'metadata')) {
                $table->json('metadata')->nullable()->after('cancellation_reason');
            }
        });

        // 6. Create fee_heads table
        Schema::create('fee_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('category'); // e.g. admission, tuition, registration, exam, discount, scholarship
            $table->decimal('default_amount', 14, 2)->nullable();
            $table->string('applies_to')->default('both'); // new_enrollment, monthly_installment, both
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_refundable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('campus_id')->nullable()->index();
            $table->timestamps();
        });

        // 7. Create fee_voucher_items table
        Schema::create('fee_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_voucher_id')->index();
            $table->unsignedBigInteger('fee_head_id')->nullable()->index();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_amount', 14, 2);
            $table->decimal('amount', 14, 2);
            $table->string('adjustment_type')->nullable(); // e.g. debit, credit, discount
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 8. Create fee_voucher_audits table
        Schema::create('fee_voucher_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_voucher_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action'); // created, updated, printed, paid, cancelled, voided
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 9. Update payment_allocations table columns
        Schema::table('payment_allocations', function (Blueprint $table) {
            if (Schema::hasColumn('payment_allocations', 'student_voucher_id') && !Schema::hasColumn('payment_allocations', 'fee_voucher_id')) {
                $table->renameColumn('student_voucher_id', 'fee_voucher_id');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_voucher_audits');
        Schema::dropIfExists('fee_voucher_items');
        Schema::dropIfExists('fee_heads');

        Schema::table('payment_allocations', function (Blueprint $table) {
            if (Schema::hasColumn('payment_allocations', 'fee_voucher_id') && !Schema::hasColumn('payment_allocations', 'student_voucher_id')) {
                $table->renameColumn('fee_voucher_id', 'student_voucher_id');
            }
        });

        if (Schema::hasTable('fee_vouchers') && !Schema::hasTable('student_vouchers')) {
            Schema::rename('fee_vouchers', 'student_vouchers');
        }

        Schema::table('student_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('student_vouchers', 'total_amount') && !Schema::hasColumn('student_vouchers', 'amount')) {
                $table->renameColumn('total_amount', 'amount');
            }
            if (Schema::hasColumn('student_vouchers', 'balance_amount') && !Schema::hasColumn('student_vouchers', 'balance')) {
                $table->renameColumn('balance_amount', 'balance');
            }
            $table->dropColumn([
                'uuid', 'admission_id', 'campus_id', 'course_id', 'academic_session_id', 
                'fee_structure_id', 'installment_id', 'voucher_type', 'orientation', 
                'subtotal', 'discount_amount', 'scholarship_amount', 'fine_amount', 
                'late_fee_amount', 'previous_balance', 'notes', 'generated_by', 
                'approved_by', 'approved_at', 'cancelled_by', 'cancelled_at', 
                'cancellation_reason', 'metadata', 'issue_date'
            ]);
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropColumn(['fee_voucher_id', 'status', 'metadata', 'bank_name']);
        });

        if (Schema::hasTable('fee_payments') && !Schema::hasTable('payments')) {
            Schema::rename('fee_payments', 'payments');
        }
    }
};
