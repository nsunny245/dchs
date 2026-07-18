<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_structures', 'admission_fee')) {
                $table->decimal('admission_fee', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('fee_structures', 'hostel_dues')) {
                $table->decimal('hostel_dues', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('fee_structures', 'verification_fee')) {
                $table->decimal('verification_fee', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('fee_structures', 'enrollment_fee')) {
                $table->decimal('enrollment_fee', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('fee_structures', 'examination_fee')) {
                $table->decimal('examination_fee', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('fee_structures', 'other_misc')) {
                $table->decimal('other_misc', 10, 2)->default(0.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn([
                'admission_fee',
                'hostel_dues',
                'verification_fee',
                'enrollment_fee',
                'examination_fee',
                'other_misc'
            ]);
        });
    }
};
