<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (! Schema::hasColumn('admissions', 'student_cnic_front')) {
                $table->text('student_cnic_front')->nullable();
            }
            if (! Schema::hasColumn('admissions', 'student_cnic_back')) {
                $table->text('student_cnic_back')->nullable();
            }
            if (! Schema::hasColumn('admissions', 'father_cnic_front')) {
                $table->text('father_cnic_front')->nullable();
            }
            if (! Schema::hasColumn('admissions', 'father_cnic_back')) {
                $table->text('father_cnic_back')->nullable();
            }

            if (! Schema::hasColumn('admissions', 'student_cnic_front_status')) {
                $table->string('student_cnic_front_status', 20)->default('pending');
            }
            if (! Schema::hasColumn('admissions', 'student_cnic_back_status')) {
                $table->string('student_cnic_back_status', 20)->default('pending');
            }
            if (! Schema::hasColumn('admissions', 'father_cnic_front_status')) {
                $table->string('father_cnic_front_status', 20)->default('pending');
            }
            if (! Schema::hasColumn('admissions', 'father_cnic_back_status')) {
                $table->string('father_cnic_back_status', 20)->default('pending');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn([
                'student_cnic_front',
                'student_cnic_back',
                'father_cnic_front',
                'father_cnic_back',
                'student_cnic_front_status',
                'student_cnic_back_status',
                'father_cnic_front_status',
                'father_cnic_back_status',
            ]);
        });
    }
};
