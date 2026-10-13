<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Campus;
use App\Models\ConvocationRegistration;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConvocationRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_convocation_form_is_available_and_saves_a_valid_entry(): void
    {
        [$campus, $course, $session] = $this->catalogue('Okara');

        $this->get(route('convocation.create'))
            ->assertOk()
            ->assertSee('Convocation Entry Form')
            ->assertSee($campus->name)
            ->assertSee($course->name);

        $this->post(route('convocation.store'), [
            'campus_id' => $campus->id,
            'academic_session_id' => $session->id,
            'course_id' => $course->id,
            'student_name' => 'Ayesha Khan',
            'attendee_name' => 'Muhammad Khan',
            'attendee_relation' => 'father',
            'attendee_cnic' => '35202-1234567-1',
        ])->assertRedirect(route('convocation.create'))->assertSessionHas('success');

        $this->assertDatabaseHas('convocation_registrations', [
            'campus_id' => $campus->id,
            'student_name' => 'Ayesha Khan',
            'attendee_name' => 'Muhammad Khan',
            'attendee_relation' => 'father',
            'attendee_cnic' => '35202-1234567-1',
            'status' => 'registered',
        ]);
    }

    public function test_session_must_belong_to_the_selected_campus(): void
    {
        [$campus, $course] = $this->catalogue('Okara');
        [, , $otherSession] = $this->catalogue('Haveli');

        $this->from(route('convocation.create'))->post(route('convocation.store'), [
            'campus_id' => $campus->id,
            'academic_session_id' => $otherSession->id,
            'course_id' => $course->id,
            'student_name' => 'Ayesha Khan',
            'attendee_name' => 'Muhammad Khan',
            'attendee_relation' => 'father',
            'attendee_cnic' => '35202-1234567-1',
        ])->assertRedirect(route('convocation.create'))->assertSessionHasErrors('academic_session_id');

        $this->assertDatabaseCount('convocation_registrations', 0);
    }

    public function test_global_session_can_be_used_for_any_active_campus(): void
    {
        [$campus, $course] = $this->catalogue('Okara');
        $session = AcademicSession::create([
            'campus_id' => null,
            'name' => 'Session 2026-2028',
            'is_active' => true,
        ]);

        $this->post(route('convocation.store'), [
            'campus_id' => $campus->id,
            'academic_session_id' => $session->id,
            'course_id' => $course->id,
            'student_name' => 'Ayesha Khan',
            'attendee_name' => 'Muhammad Khan',
            'attendee_relation' => 'father',
            'attendee_cnic' => '35202-1234567-1',
        ])->assertRedirect(route('convocation.create'))->assertSessionHas('success');

        $this->assertDatabaseHas('convocation_registrations', [
            'campus_id' => $campus->id,
            'academic_session_id' => $session->id,
        ]);
    }

    public function test_super_admin_sees_all_entries_and_campus_user_sees_only_their_campus(): void
    {
        [$okara, $course, $okaraSession] = $this->catalogue('Okara');
        [$haveli, , $haveliSession] = $this->catalogue('Haveli');

        foreach ([[$okara, $okaraSession, 'Okara Student'], [$haveli, $haveliSession, 'Haveli Student']] as [$campus, $session, $name]) {
            ConvocationRegistration::create([
                'campus_id' => $campus->id,
                'academic_session_id' => $session->id,
                'course_id' => $course->id,
                'student_name' => $name,
                'attendee_name' => 'Parent Name',
                'attendee_relation' => 'father',
                'attendee_cnic' => '35202-1234567-1',
            ]);
        }

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($superAdmin, 'admin')->get('/admin/convocation-registrations')
            ->assertOk()->assertSee('Okara Student')->assertSee('Haveli Student');

        $campusUser = User::factory()->create(['campus_id' => $okara->id, 'status' => true]);
        $campusUser->assignRole(Role::findOrCreate('Campus Principal', 'web'));
        $this->actingAs($campusUser, 'campus')->get('/campus/convocation-registrations')
            ->assertOk()->assertSee('Okara Student')->assertDontSee('Haveli Student');
    }

    private function catalogue(string $city): array
    {
        $campus = Campus::create([
            'name' => "Daniyal College {$city}",
            'code' => strtoupper(substr($city, 0, 3)).fake()->unique()->numberBetween(10, 99),
            'city' => $city,
            'is_active' => true,
        ]);
        $course = Course::firstOrCreate(
            ['code' => 'CNA'],
            ['name' => 'Certified Nursing Assistant', 'duration_months' => 24, 'is_active' => true],
        );
        $session = AcademicSession::create([
            'campus_id' => $campus->id,
            'name' => "Session 2026 {$city}",
            'is_active' => true,
        ]);

        return [$campus, $course, $session];
    }
}
