<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('campuses', 'code')) {
            Schema::table('campuses', function (Blueprint $table) {
                $table->string('code')->nullable()->after('name');
            });
        }

        // 1. Extend existing staff table
        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            if (!Schema::hasColumn('staff', 'employee_id')) {
                $table->string('employee_id')->nullable()->after('campus_id');
            }
            if (!Schema::hasColumn('staff', 'full_name')) {
                $table->string('full_name')->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('staff', 'joining_date')) {
                $table->date('joining_date')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('staff', 'father_or_spouse_name')) {
                $table->string('father_or_spouse_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('staff', 'cnic')) {
                $table->string('cnic')->nullable()->after('father_or_spouse_name');
            }
            if (!Schema::hasColumn('staff', 'cnic_issue_date')) {
                $table->date('cnic_issue_date')->nullable()->after('cnic');
            }
            if (!Schema::hasColumn('staff', 'cnic_expiry_date')) {
                $table->date('cnic_expiry_date')->nullable()->after('cnic_issue_date');
            }
            if (!Schema::hasColumn('staff', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('cnic_expiry_date');
            }
            if (!Schema::hasColumn('staff', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('staff', 'marital_status')) {
                $table->string('marital_status')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('staff', 'phone')) {
                $table->string('phone')->nullable()->after('marital_status');
            }
            if (!Schema::hasColumn('staff', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('staff', 'current_address')) {
                $table->text('current_address')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('staff', 'permanent_address')) {
                $table->text('permanent_address')->nullable()->after('current_address');
            }
            if (!Schema::hasColumn('staff', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('permanent_address');
            }
            if (!Schema::hasColumn('staff', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('staff', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_relationship');
            }
            if (!Schema::hasColumn('staff', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('emergency_contact_phone');
            }
            if (!Schema::hasColumn('staff', 'staff_category')) {
                $table->string('staff_category')->default('teaching')->after('photo_path');
            }
            if (!Schema::hasColumn('staff', 'record_status')) {
                $table->string('record_status')->default('draft')->after('staff_category');
            }
            if (!Schema::hasColumn('staff', 'completion_percentage')) {
                $table->integer('completion_percentage')->default(0)->after('record_status');
            }
        });

        // 2. Teacher Academic Profile
        Schema::create('teacher_academics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('highest_qualification')->nullable();
            $table->string('degree_title')->nullable();
            $table->string('specialization')->nullable();
            $table->string('institution')->nullable();
            $table->integer('passing_year')->nullable();
            $table->decimal('teaching_experience_years', 4, 1)->default(0);
            $table->decimal('clinical_experience_years', 4, 1)->default(0);
            $table->string('previous_employer')->nullable();
            $table->string('previous_designation')->nullable();
            $table->decimal('last_drawn_salary', 10, 2)->nullable();
            $table->text('professional_summary')->nullable();
            $table->timestamps();
        });

        // 3. Professional Registrations
        Schema::create('professional_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('registration_body')->nullable();
            $table->string('registration_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('pending');
            $table->string('document_path')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 4. Teacher Subject Pivot
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->string('subject_name')->nullable();
            $table->timestamps();
        });

        // 5. Teacher Programme Pivot
        Schema::create('teacher_programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->timestamps();
        });

        // 6. Employment Records (Audit & History)
        Schema::create('employment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->foreignId('programme_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('designation');
            $table->foreignId('reporting_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employment_type')->default('full_time');
            $table->string('appointment_status')->default('probation');
            $table->date('joining_date');
            $table->date('probation_start_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('shift')->nullable();
            $table->string('biometric_id')->nullable();
            $table->decimal('weekly_working_hours', 5, 2)->default(40.00);
            $table->decimal('weekly_teaching_hours', 5, 2)->default(20.00);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_current')->default(true);
            $table->string('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 7. Salary Records (Audit & Protected History)
        Schema::create('salary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('currency')->default('PKR');
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('probation_salary', 10, 2)->nullable();
            $table->decimal('house_allowance', 10, 2)->default(0);
            $table->decimal('transport_allowance', 10, 2)->default(0);
            $table->decimal('medical_allowance', 10, 2)->default(0);
            $table->decimal('other_allowance', 10, 2)->default(0);
            $table->decimal('recurring_deduction', 10, 2)->default(0);
            $table->decimal('tax_deduction', 10, 2)->default(0);
            $table->decimal('statutory_deduction', 10, 2)->default(0);
            $table->decimal('other_deduction', 10, 2)->default(0);
            $table->string('payment_method')->default('bank');
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('account_title')->nullable();
            $table->text('account_number_encrypted')->nullable();
            $table->text('iban_encrypted')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->date('review_date')->nullable();
            $table->integer('employee_notice_days')->default(30);
            $table->integer('college_notice_days')->default(30);
            $table->string('status')->default('approved');
            $table->string('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 8. Staff Confidential Documents
        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('title');
            $table->string('original_filename')->nullable();
            $table->string('stored_filename')->nullable();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 9. Leave Requests Workflow
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('day_type')->default('full_day');
            $table->decimal('requested_days', 4, 1);
            $table->decimal('approved_days', 4, 1)->nullable();
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('campus_note')->nullable();
            $table->string('payroll_impact')->default('paid');
            $table->string('status')->default('pending');
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('submitted_at')->useCurrent();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->timestamps();
        });

        // 10. Attendance Corrections Workflow
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->date('attendance_date');
            $table->json('original_data')->nullable();
            $table->json('requested_data')->nullable();
            $table->json('approved_data')->nullable();
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->timestamps();
        });

        // 11. Agreement Templates
        Schema::create('agreement_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('document_type');
            $table->string('version')->default('1.0');
            $table->longText('body')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 12. Agreement Versions & Signed Copies
        Schema::create('agreement_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('agreement_template_id')->nullable()->constrained('agreement_templates')->nullOnDelete();
            $table->string('agreement_number')->unique();
            $table->integer('version')->default(1);
            $table->string('appointment_status');
            $table->foreignId('salary_record_id')->nullable()->constrained('salary_records')->nullOnDelete();
            $table->foreignId('employment_record_id')->nullable()->constrained('employment_records')->nullOnDelete();
            $table->string('generated_pdf_path')->nullable();
            $table->string('signed_pdf_path')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supersedes_id')->nullable()->constrained('agreement_versions')->nullOnDelete();
            $table->longText('content_snapshot')->nullable();
            $table->string('file_hash')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_versions');
        Schema::dropIfExists('agreement_templates');
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('staff_documents');
        Schema::dropIfExists('salary_records');
        Schema::dropIfExists('employment_records');
        Schema::dropIfExists('teacher_programmes');
        Schema::dropIfExists('teacher_subjects');
        Schema::dropIfExists('professional_registrations');
        Schema::dropIfExists('teacher_academics');

        Schema::table('staff', function (Blueprint $table) {
            $columns = [
                'father_or_spouse_name', 'cnic', 'cnic_issue_date', 'cnic_expiry_date',
                'date_of_birth', 'gender', 'marital_status', 'whatsapp', 'current_address',
                'permanent_address', 'emergency_contact_name', 'emergency_contact_relationship',
                'emergency_contact_phone', 'photo_path', 'staff_category', 'record_status', 'completion_percentage'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('staff', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
