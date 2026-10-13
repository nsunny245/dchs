<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocation_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->string('student_name');
            $table->string('attendee_name');
            $table->string('attendee_relation', 40);
            $table->string('attendee_cnic', 15);
            $table->enum('status', ['registered', 'checked_in', 'cancelled'])->default('registered');
            $table->timestamps();

            $table->index(['campus_id', 'status']);
            $table->index('attendee_cnic');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocation_registrations');
    }
};
