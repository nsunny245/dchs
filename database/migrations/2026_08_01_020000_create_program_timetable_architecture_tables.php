<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Subjects table
        if (!Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->string('code')->nullable();
                $table->string('name');
                $table->string('semester_year')->default('Year 1');
                $table->integer('credit_hours')->default(3);
                $table->integer('weekly_periods')->default(4);
                $table->string('subject_type')->default('mandatory');
                $table->string('default_class_type')->default('Theory');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Rooms table
        if (!Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('room_type')->default('classroom');
                $table->integer('capacity')->default(50);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Timetable periods table
        if (!Schema::hasTable('timetable_periods')) {
            Schema::create('timetable_periods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->cascadeOnDelete();
                $table->string('name');
                $table->time('start_time');
                $table->time('end_time');
                $table->integer('sort_order')->default(1);
                $table->boolean('is_break')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Drop legacy timetables table if simple format
        Schema::dropIfExists('timetable_conflict_overrides');
        Schema::dropIfExists('timetable_activity_logs');
        Schema::dropIfExists('timetable_slots');
        Schema::dropIfExists('timetable_subjects');
        Schema::dropIfExists('timetables');

        // 5. Create new program-wise Timetables header table
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->string('batch_name')->nullable();
            $table->string('semester_name')->default('Year 1');
            $table->string('section_name')->default('Section A');
            $table->string('shift')->default('morning');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->json('working_days')->nullable();
            $table->integer('default_period_duration')->default(45);
            $table->string('status')->default('draft'); // draft, pending_approval, published, archived
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['campus_id', 'course_id', 'status']);
        });

        // 6. Timetable subjects table
        Schema::create('timetable_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('subject_code')->nullable();
            $table->string('subject_name');
            $table->foreignId('default_teacher_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->integer('required_periods_per_week')->default(4);
            $table->integer('scheduled_periods')->default(0);
            $table->string('class_type')->default('Theory');
            $table->boolean('is_mandatory')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 7. Timetable slots table
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->foreignId('timetable_subject_id')->nullable()->constrained('timetable_subjects')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('subject_name');
            $table->foreignId('teacher_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('class_type')->default('Theory');
            $table->string('day_of_week'); // monday, tuesday, wednesday, thursday, friday, saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('period_count')->default(1);
            $table->text('notes')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['timetable_id', 'day_of_week']);
            $table->index(['teacher_id', 'day_of_week', 'start_time', 'end_time']);
            $table->index(['room_id', 'day_of_week', 'start_time', 'end_time']);
        });

        // 8. Timetable conflict overrides table
        Schema::create('timetable_conflict_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_slot_id')->constrained('timetable_slots')->cascadeOnDelete();
            $table->string('conflict_type');
            $table->string('conflicting_record_type')->nullable();
            $table->unsignedBigInteger('conflicting_record_id')->nullable();
            $table->text('reason');
            $table->foreignId('overridden_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 9. Timetable activity logs table
        Schema::create('timetable_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_activity_logs');
        Schema::dropIfExists('timetable_conflict_overrides');
        Schema::dropIfExists('timetable_slots');
        Schema::dropIfExists('timetable_subjects');
        Schema::dropIfExists('timetables');
        Schema::dropIfExists('timetable_periods');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('subjects');
    }
};
