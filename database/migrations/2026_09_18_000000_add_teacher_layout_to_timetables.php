<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->json('teacher_layout')->nullable();
            $table->unsignedInteger('layout_version')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn(['teacher_layout', 'layout_version']);
        });
    }
};
