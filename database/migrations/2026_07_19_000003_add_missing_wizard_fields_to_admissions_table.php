<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (!Schema::hasColumn('admissions', 'father_occupation')) {
                $table->string('father_occupation')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'emergency_contact')) {
                $table->string('emergency_contact')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'character_certificate_copy')) {
                $table->string('character_certificate_copy')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'character_certificate_copy_status')) {
                $table->string('character_certificate_copy_status')->default('pending');
            }
            if (!Schema::hasColumn('admissions', 'city')) {
                $table->string('city')->default('Okara');
            }
            if (!Schema::hasColumn('admissions', 'concession_type')) {
                $table->string('concession_type')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'concession_amount')) {
                $table->decimal('concession_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('admissions', 'concession_reason')) {
                $table->text('concession_reason')->nullable();
            }
            if (!Schema::hasColumn('admissions', 'concession_status')) {
                $table->string('concession_status')->default('pending');
            }
            if (!Schema::hasColumn('admissions', 'concession_approver')) {
                $table->string('concession_approver')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn([
                'father_occupation',
                'emergency_contact',
                'character_certificate_copy',
                'character_certificate_copy_status',
                'city',
                'concession_type',
                'concession_amount',
                'concession_reason',
                'concession_status',
                'concession_approver'
            ]);
        });
    }
};
