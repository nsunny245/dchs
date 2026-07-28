<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE staff MODIFY user_id BIGINT UNSIGNED NULL;');
        }

        if (!Schema::hasColumn('campuses', 'code')) {
            Schema::table('campuses', function (Blueprint $table) {
                $table->string('code')->nullable()->after('name');
            });
        }

        Schema::table('staff', function (Blueprint $table) {
            if (!Schema::hasColumn('staff', 'employee_id')) {
                $table->string('employee_id')->nullable()->after('campus_id');
            }
            if (!Schema::hasColumn('staff', 'full_name')) {
                $table->string('full_name')->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('staff', 'father_or_spouse_name')) {
                $table->string('father_or_spouse_name')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('staff', 'cnic')) {
                $table->string('cnic')->nullable()->after('father_or_spouse_name');
            }
            if (!Schema::hasColumn('staff', 'cnic_issue_date')) {
                $table->date('cnic_issue_date')->nullable()->after('cnic');
            }
            if (!Schema::hasColumn('staff', 'cnic_expiry_date')) {
                $table->date('cnic_expiry_date')->nullable()->after('cnic_issue_date');
            }
            if (!Schema::hasColumn('staff', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('cnic_expiry_date');
            }
            if (!Schema::hasColumn('staff', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('staff', 'marital_status')) {
                $table->string('marital_status')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('staff', 'phone')) {
                $table->string('phone')->nullable()->after('marital_status');
            }
            if (!Schema::hasColumn('staff', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('staff', 'current_address')) {
                $table->text('current_address')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('staff', 'permanent_address')) {
                $table->text('permanent_address')->nullable()->after('current_address');
            }
            if (!Schema::hasColumn('staff', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('permanent_address');
            }
            if (!Schema::hasColumn('staff', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('staff', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_relationship');
            }
            if (!Schema::hasColumn('staff', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('emergency_contact_phone');
            }
            if (!Schema::hasColumn('staff', 'staff_category')) {
                $table->string('staff_category')->default('teaching')->after('photo_path');
            }
            if (!Schema::hasColumn('staff', 'record_status')) {
                $table->string('record_status')->default('draft')->after('staff_category');
            }
            if (!Schema::hasColumn('staff', 'completion_percentage')) {
                $table->integer('completion_percentage')->default(0)->after('record_status');
            }
            if (!Schema::hasColumn('staff', 'joining_date')) {
                $table->date('joining_date')->nullable()->after('completion_percentage');
            }
        });
    }

    public function down(): void
    {
        // Safe migration
    }
};
