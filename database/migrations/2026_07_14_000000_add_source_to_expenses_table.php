<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('expense_source')->default('college_revenue');
            $table->decimal('college_revenue_amount', 12, 2)->nullable();
            $table->decimal('chairman_naveed_amount', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['expense_source', 'college_revenue_amount', 'chairman_naveed_amount']);
        });
    }
};
