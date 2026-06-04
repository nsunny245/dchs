<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->constrained()->restrictOnDelete();
            $table->integer('installment_no');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->enum('status', ['unpaid', 'paid', 'overdue', 'partial'])->default('unpaid');
            $table->string('transaction_id')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('fee_payments'); }
};
