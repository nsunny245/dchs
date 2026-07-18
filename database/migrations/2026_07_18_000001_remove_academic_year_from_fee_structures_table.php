<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // 1. Create the new unique key first (so campus_id foreign key remains indexed)
            $table->unique(['campus_id', 'course_id'], 'fee_structures_campus_id_course_id_unique');
            
            // 2. Drop the old unique key
            $table->dropUnique('fee_structures_campus_id_course_id_academic_year_unique');
            
            // 3. Drop the column
            $table->dropColumn('academic_year');
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // Restore academic_year column
            $table->string('academic_year')->default('2026-2028');
            
            // Re-create old unique key first
            $table->unique(['campus_id', 'course_id', 'academic_year'], 'fee_structures_campus_id_course_id_academic_year_unique');
            
            // Drop new unique key
            $table->dropUnique('fee_structures_campus_id_course_id_unique');
        });
    }
};
