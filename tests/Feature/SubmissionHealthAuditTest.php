<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionHealthAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reports_an_admission_saved_without_a_student_without_changing_it(): void
    {
        $campus = Campus::create(['name' => 'Audit Campus', 'city' => 'Okara']);
        $course = Course::create(['code' => 'AUD', 'name' => 'Audit Course', 'duration_months' => 12]);
        $session = AcademicSession::create(['campus_id' => $campus->id, 'name' => 'Audit Session']);
        $admission = Admission::create([
            'applicant_name' => 'Incomplete Applicant',
            'father_name' => 'Parent Name',
            'cnic' => '00000-0000000-0',
            'dob' => '2000-01-01',
            'gender' => 'male',
            'phone' => '03000000000',
            'address' => 'Audit address',
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'academic_session_id' => $session->id,
            'status' => 'pending',
        ]);

        $this->artisan('dgc:audit-submissions')
            ->expectsOutputToContain('Admissions saved without a student: 1')
            ->assertSuccessful();

        $this->assertDatabaseHas('admissions', ['id' => $admission->id, 'status' => 'pending']);
        $this->assertDatabaseMissing('students', ['admission_id' => $admission->id]);
    }

    public function test_repair_requires_an_existing_actor(): void
    {
        $this->artisan('dgc:audit-submissions', ['--repair' => true, '--actor' => 999999])
            ->expectsOutputToContain('--actor must be the ID of an existing administrator')
            ->assertFailed();
    }
}
