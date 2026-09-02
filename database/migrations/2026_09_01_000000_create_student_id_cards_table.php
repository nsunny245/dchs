<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_id_cards')) {
            return;
        }

        Schema::create('student_id_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('card_number')->unique();
            $table->date('issue_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->string('qr_token', 64)->nullable()->unique();
            $table->string('barcode_value')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('last_printed_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_id_cards');
    }
};
