<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('phone');
            $table->enum('relation_to_student', ['self', 'father', 'mother', 'guardian', 'relative', 'other'])->default('self');
            $table->enum('came_by', ['walk_in', 'social_media', 'banner_poster', 'friend_alumni', 'newspaper', 'other'])->default('walk_in');
            $table->foreignId('desired_course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->enum('status', ['new', 'contacted', 'followed_up', 'admitted', 'closed'])->default('new');
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_queries');
    }
};
