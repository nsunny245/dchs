<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->string('academic_year');
            $table->decimal('total_fee', 10, 2);
            $table->integer('installment_count')->default(3);
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['campus_id', 'course_id', 'academic_year']);
        });
    }
    public function down(): void { Schema::dropIfExists('fee_structures'); }
};
