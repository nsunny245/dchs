<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('enrollment_number')->unique();
            $table->string('full_name');
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->string('batch_year')->nullable();
            $table->enum('status', ['active', 'graduated', 'dropped', 'suspended'])->default('active');
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['campus_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('students'); }
};
