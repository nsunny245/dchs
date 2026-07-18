<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_structures', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('fee_structures', 'academic_session_id')) {
                $table->unsignedBigInteger('academic_session_id')->nullable()->index();
            }
            if (!Schema::hasColumn('fee_structures', 'shift')) {
                $table->string('shift')->nullable();
            }
            if (!Schema::hasColumn('fee_structures', 'effective_date')) {
                $table->date('effective_date')->nullable();
            }
            if (!Schema::hasColumn('fee_structures', 'expiry_date')) {
                $table->date('expiry_date')->nullable();
            }
            if (!Schema::hasColumn('fee_structures', 'status')) {
                $table->string('status')->default('active'); // active, inactive
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'academic_session_id',
                'shift',
                'effective_date',
                'expiry_date',
                'status'
            ]);
        });
    }
};
