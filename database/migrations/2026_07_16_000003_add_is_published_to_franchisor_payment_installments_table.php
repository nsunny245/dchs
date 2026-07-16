<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('franchisor_payment_installments', 'is_published')) {
            Schema::table('franchisor_payment_installments', function (Blueprint $table) {
                $table->boolean('is_published')->default(true)->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('franchisor_payment_installments', 'is_published')) {
            Schema::table('franchisor_payment_installments', function (Blueprint $table) {
                $table->dropColumn('is_published');
            });
        }
    }
};
