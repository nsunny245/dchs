<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            // Graduation academic columns
            if (!Schema::hasColumn('admissions', 'grad_degree')) {
                $table->string('grad_degree')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'grad_year')) {
                $table->string('grad_year')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'grad_roll_no')) {
                $table->string('grad_roll_no')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'grad_board')) {
                $table->string('grad_board')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'grad_obtained_marks')) {
                $table->integer('grad_obtained_marks')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'grad_total_marks')) {
                $table->integer('grad_total_marks')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'grad_grade')) {
                $table->string('grad_grade')->nullable();
            }

            // JSON Academic details (for dynamic repeater)
            if (!Schema::hasColumn('admissions', 'academic_details')) {
                $table->json('academic_details')->nullable();
            }

            // Custom overrides for course fee plan
            if (!Schema::hasColumn('admissions', 'custom_installment_count')) {
                $table->integer('custom_installment_count')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'custom_admission_fee')) {
                $table->decimal('custom_admission_fee', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('admissions', 'custom_tuition_fee')) {
                $table->decimal('custom_tuition_fee', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('admissions', 'custom_verification_fee')) {
                $table->decimal('custom_verification_fee', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('admissions', 'custom_enrollment_fee')) {
                $table->decimal('custom_enrollment_fee', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('admissions', 'custom_examination_fee')) {
                $table->decimal('custom_examination_fee', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('admissions', 'custom_other_misc')) {
                $table->decimal('custom_other_misc', 10, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn([
                'grad_degree',
                'grad_year',
                'grad_roll_no',
                'grad_board',
                'grad_obtained_marks',
                'grad_total_marks',
                'grad_grade',
                'academic_details',
                'custom_installment_count',
                'custom_admission_fee',
                'custom_tuition_fee',
                'custom_verification_fee',
                'custom_enrollment_fee',
                'custom_examination_fee',
                'custom_other_misc'
            ]);
        });
    }
};
