<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (!Schema::hasColumn('admissions', 'cnic_copy_status')) {
                $table->string('cnic_copy_status')->default('pending');
            }
            if (!Schema::hasColumn('admissions', 'father_cnic_copy_status')) {
                $table->string('father_cnic_copy_status')->default('pending');
            }
            if (!Schema::hasColumn('admissions', 'matric_copy_status')) {
                $table->string('matric_copy_status')->default('pending');
            }
            if (!Schema::hasColumn('admissions', 'inter_copy_status')) {
                $table->string('inter_copy_status')->default('pending');
            }
            if (!Schema::hasColumn('admissions', 'domicile_copy_status')) {
                $table->string('domicile_copy_status')->default('pending');
            }
            if (!Schema::hasColumn('admissions', 'other_docs_status')) {
                $table->string('other_docs_status')->default('pending');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn([
                'cnic_copy_status',
                'father_cnic_copy_status',
                'matric_copy_status',
                'inter_copy_status',
                'domicile_copy_status',
                'other_docs_status'
            ]);
        });
    }
};
