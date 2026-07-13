<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visitor_queries', function (Blueprint $table) {
            // Convert came_by from enum to string to support 'website'
            $table->string('came_by')->default('walk_in')->change();
            
            // Add extra fields needed to convert to admission
            $table->string('father_name')->nullable();
            $table->string('cnic')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->text('previous_education')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('visitor_queries', function (Blueprint $table) {
            $table->dropColumn([
                'father_name',
                'cnic',
                'dob',
                'gender',
                'address',
                'previous_education'
            ]);
        });
    }
};
