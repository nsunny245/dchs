<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Room;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Services\Timetable\TimetableConflictService;
use App\Services\Timetable\TimetableBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class ProgramTimetableTest extends TestCase
{
    use RefreshDatabase;

    protected Campus $campus;
    protected Course $course;
    protected User $admin;
    protected Staff $teacher;
    protected Room $room;
    protected Subject $subject1;
    protected Subject $subject2;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Campus Principal', 'guard_name' => 'web']);

        $this->campus = Campus::create(['name' => 'Test Campus', 'code' => 'TC', 'city' => 'Lahore']);
        $this->course = Course::create(['code' => 'LHV', 'name' => 'Lady Health Visitor', 'duration_months' => 24]);

        $this->admin = User::factory()->create(['campus_id' => $this->campus->id]);
        $this->admin->assignRole('Campus Principal');

        $teacherUser = User::factory()->create(['name' => 'Prof. Ali', 'campus_id' => $this->campus->id]);
        $this->teacher = Staff::create([
            'campus_id' => $this->campus->id,
            'user_id' => $teacherUser->id,
            'employee_id' => 'DGC-TC-TEA-0001',
            'staff_category' => 'teaching',
            'designation' => 'Senior Lecturer',
            'hire_date' => now()->format('Y-m-d'),
        ]);

        $this->room = Room::create([
            'campus_id' => $this->campus->id,
            'name' => 'Lecture Hall 1',
            'room_type' => 'classroom',
            'capacity' => 50,
            'is_active' => true,
        ]);

        $this->subject1 = Subject::create([
            'course_id' => $this->course->id,
            'code' => 'LHV-101',
            'name' => 'Anatomy & Physiology',
            'semester_year' => 'Year 1',
            'credit_hours' => 3,
            'weekly_periods' => 4,
            'default_class_type' => 'Theory',
            'is_active' => true,
        ]);

        $this->subject2 = Subject::create([
            'course_id' => $this->course->id,
            'code' => 'LHV-102',
            'name' => 'Community Health Nursing',
            'semester_year' => 'Year 1',
            'credit_hours' => 3,
            'weekly_periods' => 4,
            'default_class_type' => 'Theory',
            'is_active' => true,
        ]);
    }

    public function test_timetable_header_creation_and_subject_sync(): void
    {
        $timetable = Timetable::create([
            'title' => 'LHV Year 1 - Section A Timetable',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'semester_name' => 'Year 1',
            'section_name' => 'Section A',
            'effective_from' => now()->format('Y-m-d'),
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        TimetableBuilderService::syncSubjects(
            $timetable,
            [$this->subject1->id, $this->subject2->id],
            [$this->subject1->id => $this->teacher->id],
            [$this->subject1->id => 4, $this->subject2->id => 4]
        );

        $this->assertDatabaseHas('timetables', ['id' => $timetable->id, 'title' => 'LHV Year 1 - Section A Timetable']);
        $this->assertDatabaseHas('timetable_subjects', ['timetable_id' => $timetable->id, 'subject_id' => $this->subject1->id]);
    }

    public function test_teacher_and_room_conflict_detection(): void
    {
        $timetable = Timetable::create([
            'title' => 'LHV Year 1 - Section A Timetable',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'semester_name' => 'Year 1',
            'section_name' => 'Section A',
            'effective_from' => now()->format('Y-m-d'),
            'status' => 'published',
        ]);

        // Create slot 1
        TimetableSlot::create([
            'timetable_id' => $timetable->id,
            'subject_id' => $this->subject1->id,
            'subject_name' => $this->subject1->name,
            'teacher_id' => $this->teacher->id,
            'room_id' => $this->room->id,
            'day_of_week' => 'monday',
            'start_time' => '08:30:00',
            'end_time' => '09:15:00',
        ]);

        // Attempting to schedule same teacher at same time should trigger conflict
        $validation = TimetableConflictService::validateSlot([
            'timetable_id' => $timetable->id,
            'day_of_week' => 'monday',
            'start_time' => '08:30:00',
            'end_time' => '09:15:00',
            'teacher_id' => $this->teacher->id,
            'room_id' => $this->room->id,
        ]);

        $this->assertTrue($validation['has_errors']);
        $this->assertCount(3, $validation['errors']); // Teacher, Room, and Section conflicts detected
    }

    public function test_pdf_export_route(): void
    {
        $timetable = Timetable::create([
            'title' => 'LHV Year 1 - Section A Timetable',
            'campus_id' => $this->campus->id,
            'course_id' => $this->course->id,
            'semester_name' => 'Year 1',
            'section_name' => 'Section A',
            'effective_from' => now()->format('Y-m-d'),
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin, 'campus')->get(route('pdf.timetable', $timetable->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
