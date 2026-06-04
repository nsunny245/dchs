<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->string('designation');
            $table->string('department')->nullable();
            $table->date('hire_date');
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('status', ['active', 'on_leave', 'terminated'])->default('active');
            $table->text('bio')->nullable();
            $table->timestamps();
            $table->index(['campus_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('staff'); }
};
