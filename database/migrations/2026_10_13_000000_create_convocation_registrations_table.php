<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('convocation_registrations')) {
            return;
        }

        Schema::create('convocation_registrations', function (Blueprint $table) {
            $table->id();
            // Indexes preserve fast dashboard filtering while avoiding foreign
            // key failures on legacy databases with mixed engines or ID types.
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->unsignedBigInteger('course_id');
            $table->string('student_name');
            $table->string('attendee_name');
            $table->string('attendee_relation', 40);
            $table->string('attendee_cnic', 15);
            $table->enum('status', ['registered', 'checked_in', 'cancelled'])->default('registered');
            $table->timestamps();

            $table->index(['campus_id', 'status']);
            $table->index('academic_session_id');
            $table->index('course_id');
            $table->index('attendee_cnic');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocation_registrations');
    }
};
