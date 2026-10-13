<?php

namespace Tests\Feature;

use App\Filament\Resources\TimetableResource\Pages\TeacherTimetable;
use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Timetable;
use App\Models\User;
use App\Services\Timetable\TeacherTimetableLayout;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherTimetableTest extends TestCase
{
    use RefreshDatabase;

    private Campus $campus;

    private Course $course;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->campus = Campus::create(['name' => 'Layout Campus', 'code' => 'LAY', 'city' => 'Okara']);
        $this->course = Course::create(['name' => 'Pharmacy', 'code' => 'LAY-P', 'duration_months' => 24]);
        $this->user = User::factory()->create(['campus_id' => $this->campus->id]);
        $this->user->assignRole(Role::findOrCreate('Campus Principal', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('campus'));
        $this->actingAs($this->user, 'campus');
    }

    private function editor()
    {
        return Livewire::test(TeacherTimetable::class)
            ->set('campusId', $this->campus->id)
            ->set('courseId', $this->course->id);
    }

    public function test_document_template_has_distinct_day_groups_and_sunday_hours(): void
    {
        $layout = TeacherTimetableLayout::validate(TeacherTimetableLayout::template());
        $this->assertCount(3, $layout['groups']);
        $this->assertSame(['sunday'], $layout['groups'][2]['days']);
        $this->assertSame('15:00', $layout['groups'][2]['periods'][5]['end']);
        $this->assertSame(['tuesday'], $layout['groups'][0]['teachers'][1]['cells'][7]['days']);
        $this->assertTrue($layout['groups'][0]['periods'][3]['is_break']);
    }

    public function test_edit_save_reopen_publish_and_pdf_preserve_manual_content(): void
    {
        $page = $this->editor()->set('timetableTitle', 'September teacher sheet')
            ->set('sheet.groups.2.teachers.0.cells.5.text', 'Editable Sunday Subject / Batch 26')
            ->call('save')->assertHasNoErrors();
        $id = $page->get('recordId');
        $this->assertNotNull($id);
        $this->assertDatabaseHas('timetables', ['id' => $id, 'status' => 'draft', 'layout_version' => 1]);
        Livewire::test(TeacherTimetable::class, ['record' => $id])
            ->assertSet('sheet.groups.2.teachers.0.cells.5.text', 'Editable Sunday Subject / Batch 26')
            ->call('save', true)->assertHasNoErrors();
        $this->assertDatabaseHas('timetables', ['id' => $id, 'status' => 'published', 'layout_version' => 2]);
        $record = Timetable::findOrFail($id);
        $html = view('pdf.timetable', ['timetable' => $record, 'periods' => collect()])->render();
        $this->assertStringContainsString('Editable Sunday Subject / Batch 26', $html);
        $this->assertStringContainsString('Sunday', $html);
        $this->get(route('pdf.timetable', $id))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_period_and_teacher_operations_keep_cells_aligned(): void
    {
        $page = $this->editor()->call('addPeriod', 2)->call('addTeacher', 2);
        $this->assertCount(7, $page->get('sheet')['groups'][2]['teachers'][3]['cells']);
        $page->set('sheet.groups.2.teachers.3.name', 'New Teacher')
            ->set('sheet.groups.2.teachers.3.cells.0.text', 'New subject')
            ->call('moveTeacher', 2, 3, -1);
        $this->assertSame('New subject', $page->get('sheet')['groups'][2]['teachers'][2]['cells'][0]['text']);
        $page->call('removePeriod', 2, 1)->call('removeTeacher', 2, 0)->call('save')->assertHasNoErrors();
        foreach ($page->get('sheet')['groups'][2]['teachers'] as $teacher) {
            $this->assertCount(6, $teacher['cells']);
        }
    }

    public function test_overlapping_periods_are_rejected_without_saving(): void
    {
        $this->editor()->set('sheet.groups.0.periods.1.start', '08:45')
            ->call('save')->assertHasErrors(['layout.groups.0.periods.1.start']);
        $this->assertDatabaseCount('timetables', 0);
    }

    public function test_day_outside_group_and_occupied_break_are_rejected(): void
    {
        $this->editor()->set('sheet.groups.2.teachers.0.cells.0.days', ['monday'])
            ->set('sheet.groups.2.periods.0.is_break', true)
            ->call('save')->assertHasErrors(['layout.groups.2.teachers.0.cells.0.days', 'layout.groups.2.teachers.0.cells.0.text']);
    }

    public function test_teacher_overlap_between_groups_is_rejected(): void
    {
        $layout = TeacherTimetableLayout::template();
        $layout['groups'][2]['days'] = ['monday'];
        $this->expectException(ValidationException::class);
        TeacherTimetableLayout::validate($layout);
    }

    public function test_stale_editor_cannot_overwrite_newer_save(): void
    {
        $id = $this->editor()->call('save')->get('recordId');
        $first = Livewire::test(TeacherTimetable::class, ['record' => $id]);
        $second = Livewire::test(TeacherTimetable::class, ['record' => $id]);
        $first->set('timetableTitle', 'First writer')->call('save')->assertHasNoErrors();
        $second->set('timetableTitle', 'Stale writer')->call('save')->assertHasErrors(['layout']);
        $this->assertSame('First writer', Timetable::findOrFail($id)->title);
    }

    public function test_campus_cannot_edit_another_campus_sheet(): void
    {
        $id = $this->editor()->call('save')->get('recordId');
        $other = Campus::create(['name' => 'Other', 'code' => 'OTHER', 'city' => 'Lahore']);
        $this->user->update(['campus_id' => $other->id]);
        $this->actingAs($this->user->fresh(), 'campus');
        $this->get('/campus/timetables/'.$id.'/teacher-layout')->assertNotFound();
    }

    public function test_admission_officer_cannot_open_teacher_editor(): void
    {
        $this->user->syncRoles([Role::findOrCreate('Admission Officer', 'web')]);
        $this->get('/campus/timetables/teacher-layout/create')->assertForbidden();
    }

    public function test_missing_document_count_uses_same_checklist_and_legacy_front_copies(): void
    {
        $admission = new Admission;
        $this->assertCount(9, $admission->missingDocumentLabels());
        $admission->cnic_copy = 'legacy-student.pdf';
        $admission->father_cnic_copy = 'legacy-father.pdf';
        $admission->student_photo = 'photo.webp';
        $this->assertCount(6, $admission->missingDocumentLabels());
        $this->assertContains('Student CNIC Back', $admission->missingDocumentLabels());
        $this->assertNotContains('Student CNIC Front', $admission->missingDocumentLabels());
        foreach (['student_cnic_back', 'father_cnic_back', 'matric_copy', 'inter_copy', 'domicile_copy', 'character_certificate_copy'] as $field) {
            $admission->$field = 'uploaded.pdf';
        }
        $this->assertSame([], $admission->missingDocumentLabels());
    }
}
