<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('admissions', 'custom_installment_interval_months')) {
                $table->unsignedTinyInteger('custom_installment_interval_months')->default(1);
            }
            if (! Schema::hasColumn('admissions', 'custom_installment_start_date')) {
                $table->date('custom_installment_start_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive so saved admission schedules are preserved.
    }
};
