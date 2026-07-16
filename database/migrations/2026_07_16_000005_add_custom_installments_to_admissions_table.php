<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('admissions', 'custom_installments')) {
            Schema::table('admissions', function (Blueprint $table) {
                $table->json('custom_installments')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('admissions', 'custom_installments')) {
            Schema::table('admissions', function (Blueprint $table) {
                $table->dropColumn('custom_installments');
            });
        }
    }
};
