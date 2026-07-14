<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Backfill existing expenses to set default source as college_revenue and set college_revenue_amount
        DB::table('expenses')
            ->whereNull('college_revenue_amount')
            ->update([
                'expense_source' => 'college_revenue',
                'college_revenue_amount' => DB::raw('amount'),
                'chairman_naveed_amount' => 0.00
            ]);
    }

    public function down(): void
    {
        // No down actions needed for backfill
    }
};
