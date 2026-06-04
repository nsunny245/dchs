<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->enum('exam_type', ['midterm', 'final', 'practical', 'sessional']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_marks');
            $table->timestamps();
            $table->index(['campus_id', 'exam_type']);
        });
    }
    public function down(): void { Schema::dropIfExists('exams'); }
};
