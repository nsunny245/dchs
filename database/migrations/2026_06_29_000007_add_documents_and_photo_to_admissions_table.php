<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (!Schema::hasColumn('admissions', 'student_photo')) {
                $table->string('student_photo')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'cnic_copy')) {
                $table->string('cnic_copy')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'father_cnic_copy')) {
                $table->string('father_cnic_copy')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'matric_copy')) {
                $table->string('matric_copy')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'inter_copy')) {
                $table->string('inter_copy')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'domicile_copy')) {
                $table->string('domicile_copy')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'other_docs')) {
                $table->string('other_docs')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'student_signature')) {
                $table->string('student_signature')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'guardian_signature')) {
                $table->string('guardian_signature')->nullable();
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
