<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdmissionDocumentPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_photo_is_embedded_without_a_public_storage_symlink(): void
    {
        Storage::fake('public');

        $photo = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        );
        Storage::disk('public')->put('student-photos/student.png', $photo);

        $campus = Campus::create([
            'name' => 'Document Test Campus',
            'code' => 'DOC',
            'city' => 'Okara',
        ]);
        $course = Course::create([
            'name' => 'Document Test Course',
            'code' => 'DOC-COURSE',
            'duration_months' => 12,
        ]);
        $admission = Admission::create([
            'applicant_name' => 'Photo Test Student',
            'father_name' => 'Photo Test Guardian',
            'cnic' => '35202-1000001-1',
            'dob' => '2005-01-01',
            'gender' => 'male',
            'phone' => '03001000001',
            'address' => 'Okara',
            'course_id' => $course->id,
            'campus_id' => $campus->id,
            'student_photo' => 'student-photos/student.png',
        ]);

        $user = User::factory()->create(['campus_id' => $campus->id]);
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user, 'admin');

        $this->get(route('pdf.admission-agreement', $admission))
            ->assertOk()
            ->assertSee('data:image/png;base64,', false)
            ->assertSee('alt="Student photo"', false)
            ->assertDontSee('Affix Photo');

        $this->get(route('pdf.admission-letter', $admission))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_missing_photo_keeps_the_printable_placeholder(): void
    {
        Storage::fake('public');

        $campus = Campus::create([
            'name' => 'Missing Photo Campus',
            'code' => 'MPC',
            'city' => 'Okara',
        ]);
        $course = Course::create([
            'name' => 'Missing Photo Course',
            'code' => 'MPC-COURSE',
            'duration_months' => 12,
        ]);
        $admission = Admission::create([
            'applicant_name' => 'Missing Photo Student',
            'father_name' => 'Missing Photo Guardian',
            'cnic' => '35202-1000002-2',
            'dob' => '2005-01-01',
            'gender' => 'male',
            'phone' => '03001000002',
            'address' => 'Okara',
            'course_id' => $course->id,
            'campus_id' => $campus->id,
            'student_photo' => 'student-photos/missing.png',
        ]);

        $user = User::factory()->create(['campus_id' => $campus->id]);
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user, 'admin');

        $this->get(route('pdf.admission-agreement', $admission))
            ->assertOk()
            ->assertSee('Affix Photo');
    }
}
