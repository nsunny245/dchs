<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Disable foreign keys dynamically by driver
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Truncate dependent tables safely if they exist
        if (Schema::hasTable('fee_voucher_items')) DB::table('fee_voucher_items')->truncate();
        if (Schema::hasTable('fee_vouchers')) DB::table('fee_vouchers')->truncate();
        if (Schema::hasTable('fee_payments')) DB::table('fee_payments')->truncate();
        if (Schema::hasTable('student_fee_accounts')) DB::table('student_fee_accounts')->truncate();
        if (Schema::hasTable('fee_structures')) DB::table('fee_structures')->truncate();
        if (Schema::hasTable('fee_heads')) DB::table('fee_heads')->truncate();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 2. Safely drop foreign key constraints pointing to fee_structures
        if (DB::getDriverName() !== 'sqlite') {
            try {
                Schema::table('fee_vouchers', function (Blueprint $table) {
                    $table->dropForeign(['fee_structure_id']);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('student_fee_snapshots', function (Blueprint $table) {
                    $table->dropForeign(['fee_structure_id']);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('installment_templates', function (Blueprint $table) {
                    $table->dropForeign(['fee_structure_id']);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('fee_components', function (Blueprint $table) {
                    $table->dropForeign(['fee_structure_id']);
                });
            } catch (\Exception $e) {}
        }

        // 3. Drop and recreate fee_structures table
        Schema::dropIfExists('fee_structures');
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->unique()->constrained()->restrictOnDelete();
            $table->decimal('total_fee', 10, 2);
            $table->integer('installment_count')->default(12);
            $table->timestamps();
        });

        // 4. Re-add foreign key constraints safely
        if (DB::getDriverName() !== 'sqlite') {
            try {
                Schema::table('fee_vouchers', function (Blueprint $table) {
                    $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->nullOnDelete();
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('student_fee_snapshots', function (Blueprint $table) {
                    $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->nullOnDelete();
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('installment_templates', function (Blueprint $table) {
                    $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->nullOnDelete();
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('fee_components', function (Blueprint $table) {
                    $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->nullOnDelete();
                });
            } catch (\Exception $e) {}
        }

        // 5. Safely drop index on fee_heads before dropping the column
        try {
            Schema::table('fee_heads', function (Blueprint $table) {
                if (DB::getDriverName() === 'sqlite') {
                    $table->dropIndex('fee_heads_campus_id_index');
                } else {
                    $table->dropIndex(['campus_id']);
                }
            });
        } catch (\Exception $e) {}

        // 6. Modify fee_heads table (add course_id, drop campus_id)
        Schema::table('fee_heads', function (Blueprint $table) {
            if (Schema::hasColumn('fee_heads', 'campus_id')) {
                $table->dropColumn('campus_id');
            }
            if (!Schema::hasColumn('fee_heads', 'course_id')) {
                $table->foreignId('course_id')->nullable()->constrained()->restrictOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal logic
        Schema::table('fee_heads', function (Blueprint $table) {
            if (Schema::hasColumn('fee_heads', 'course_id')) {
                $table->dropColumn('course_id');
            }
        });
    }
};
