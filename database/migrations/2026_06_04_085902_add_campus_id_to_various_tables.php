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
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('marks', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            
            // For settings, key uniqueness should be scoped per campus.
            // Under SQLite dropping indices can fail, so check if db driver is not sqlite before dropping.
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropUnique('settings_key_unique');
            }
            $table->unique(['campus_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['campus_id', 'key']);
            if (DB::getDriverName() !== 'sqlite') {
                $table->unique('key');
            }
            $table->dropForeign(['campus_id']);
            $table->dropColumn('campus_id');
        });

        Schema::table('marks', function (Blueprint $table) {
            $table->dropForeign(['campus_id']);
            $table->dropColumn('campus_id');
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['campus_id']);
            $table->dropColumn('campus_id');
        });
    }
};
