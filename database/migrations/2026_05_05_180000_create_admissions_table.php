<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('applicant_name');
            $table->string('father_name');
            $table->string('cnic')->unique();
            $table->date('dob');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address');
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->string('previous_education')->nullable();
            $table->string('documents_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'waitlisted'])->default('pending');
            $table->decimal('merit_score', 5, 2)->nullable();
            $table->string('enrollment_no')->unique()->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();
            $table->index(['campus_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('admissions'); }
};
