<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (!Schema::hasColumn('admissions', 'academic_session_id')) {
                $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            }
            if (!Schema::hasColumn('admissions', 'roll_no')) {
                $table->string('roll_no')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'registration_no')) {
                $table->string('registration_no')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'gr_no')) {
                $table->string('gr_no')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'admission_date')) {
                $table->date('admission_date')->nullable();
            }

            if (!Schema::hasColumn('admissions', 'applicant_name_urdu')) {
                $table->string('applicant_name_urdu')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'father_name_urdu')) {
                $table->string('father_name_urdu')->nullable();
            }

            if (!Schema::hasColumn('admissions', 'father_cnic')) {
                $table->string('father_cnic')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'mother_cnic')) {
                $table->string('mother_cnic')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'mother_phone')) {
                $table->string('mother_phone')->nullable();
            }

            if (!Schema::hasColumn('admissions', 'blood_group')) {
                $table->string('blood_group')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'domicile_district')) {
                $table->string('domicile_district')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'caste')) {
                $table->string('caste')->nullable();
            }

            if (!Schema::hasColumn('admissions', 'reference')) {
                $table->string('reference')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'residence_type')) {
                $table->enum('residence_type', ['boarder', 'non_boarder'])->default('non_boarder');
            }
            if (!Schema::hasColumn('admissions', 'shift')) {
                $table->enum('shift', ['morning', 'evening'])->default('morning');
            }

            // Matric Details
            if (!Schema::hasColumn('admissions', 'matric_degree')) {
                $table->string('matric_degree')->nullable()->default('Matric Science');
            }
            if (!Schema::hasColumn('admissions', 'matric_year')) {
                $table->string('matric_year')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'matric_roll_no')) {
                $table->string('matric_roll_no')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'matric_board')) {
                $table->string('matric_board')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'matric_obtained_marks')) {
                $table->integer('matric_obtained_marks')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'matric_total_marks')) {
                $table->integer('matric_total_marks')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'matric_grade')) {
                $table->string('matric_grade')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'matric_biology_marks')) {
                $table->integer('matric_biology_marks')->nullable();
            }

            // Intermediate Details
            if (!Schema::hasColumn('admissions', 'inter_degree')) {
                $table->string('inter_degree')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'inter_year')) {
                $table->string('inter_year')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'inter_roll_no')) {
                $table->string('inter_roll_no')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'inter_board')) {
                $table->string('inter_board')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'inter_obtained_marks')) {
                $table->integer('inter_obtained_marks')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'inter_total_marks')) {
                $table->integer('inter_total_marks')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'inter_grade')) {
                $table->string('inter_grade')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            //
        });
    }
};
