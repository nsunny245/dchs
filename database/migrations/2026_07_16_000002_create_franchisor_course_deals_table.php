<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('franchisor_course_deals')) {
            Schema::create('franchisor_course_deals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('franchisor_id')->index();
                $table->unsignedBigInteger('course_id')->index();
                $table->integer('total_seats')->default(0);
                $table->decimal('per_seat_cost', 12, 2)->default(0.00);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('franchisor_course_deals');
    }
};
