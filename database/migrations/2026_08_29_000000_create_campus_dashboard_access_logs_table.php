<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_dashboard_access_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('super_admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('campus_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 191)->nullable();
            $table->timestamp('entered_at');
            $table->timestamp('exited_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['super_admin_user_id', 'entered_at'], 'campus_access_admin_entered_index');
            $table->index(['campus_id', 'entered_at'], 'campus_access_campus_entered_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_dashboard_access_logs');
    }
};
