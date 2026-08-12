<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('staff', 'is_active')) {
            Schema::table('staff', function (Blueprint $table): void {
                $table->boolean('is_active')->default(true)->after('status');
            });
        }

        if (Schema::hasColumn('staff', 'status')) {
            DB::table('staff')
                ->whereIn('status', ['terminated', 'inactive'])
                ->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: existing staff activity data is preserved.
    }
};
