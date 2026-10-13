<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('timetables', 'teacher_layout')) {
            Schema::table('timetables', function (Blueprint $table) {
                $table->json('teacher_layout')->nullable();
            });
        }
        if (! Schema::hasColumn('timetables', 'layout_version')) {
            Schema::table('timetables', function (Blueprint $table) {
                $table->unsignedInteger('layout_version')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn(['teacher_layout', 'layout_version']);
        });
    }
};
