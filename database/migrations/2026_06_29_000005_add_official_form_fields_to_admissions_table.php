<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->string('roll_no')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('gr_no')->nullable();
            $table->date('admission_date')->nullable();

            $table->string('applicant_name_urdu')->nullable();
            $table->string('father_name_urdu')->nullable();

            $table->string('father_cnic')->nullable();
            $table->string('mother_cnic')->nullable();
            $table->string('mother_phone')->nullable();

            $table->string('blood_group')->nullable();
            $table->string('domicile_district')->nullable();
            $table->string('caste')->nullable();

            $table->string('reference')->nullable();
            $table->enum('residence_type', ['boarder', 'non_boarder'])->default('non_boarder');
            $table->enum('shift', ['morning', 'evening'])->default('morning');

            // Matric Details
            $table->string('matric_degree')->nullable()->default('Matric Science');
            $table->string('matric_year')->nullable();
            $table->string('matric_roll_no')->nullable();
            $table->string('matric_board')->nullable();
            $table->integer('matric_obtained_marks')->nullable();
            $table->integer('matric_total_marks')->nullable();
            $table->string('matric_grade')->nullable();
            $table->integer('matric_biology_marks')->nullable();

            // Intermediate Details
            $table->string('inter_degree')->nullable();
            $table->string('inter_year')->nullable();
            $table->string('inter_roll_no')->nullable();
            $table->string('inter_board')->nullable();
            $table->integer('inter_obtained_marks')->nullable();
            $table->integer('inter_total_marks')->nullable();
            $table->string('inter_grade')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropForeign(['academic_session_id']);
            $table->dropColumn([
                'academic_session_id', 'roll_no', 'registration_no', 'gr_no', 'admission_date',
                'applicant_name_urdu', 'father_name_urdu', 'father_cnic', 'mother_cnic', 'mother_phone',
                'blood_group', 'domicile_district', 'caste', 'reference', 'residence_type', 'shift',
                'matric_degree', 'matric_year', 'matric_roll_no', 'matric_board', 'matric_obtained_marks', 'matric_total_marks', 'matric_grade', 'matric_biology_marks',
                'inter_degree', 'inter_year', 'inter_roll_no', 'inter_board', 'inter_obtained_marks', 'inter_total_marks', 'inter_grade'
            ]);
        });
    }
};
