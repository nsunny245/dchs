<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Course;
use App\Models\Campus;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CampusAndCourseSeeder::class);
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Core page routes
        $this->get('/')->assertStatus(200);
        $this->get('/campuses')->assertStatus(200);
        $this->get('/courses')->assertStatus(200);
        $this->get('/apply')->assertStatus(200);
        $this->get('/contact')->assertStatus(200);

        // About pages routes
        $this->get('/about/chairmans-message')->assertStatus(200);
        $this->get('/about/vision-mission')->assertStatus(200);
        $this->get('/about/accreditation')->assertStatus(200);
        $this->get('/about/leadership')->assertStatus(200);

        // Dynamic detail routes
        $campus = Campus::first();
        $this->get('/campuses/' . $campus->id)->assertStatus(200);
        
        $course = Course::first();
        $this->get('/courses/' . $course->code)->assertStatus(200);
    }

    public function test_franchisor_pages(): void
    {
        // Seed roles and users
        $this->seed(\Database\Seeders\SeedFranchisorRolesAndUsers::class);

        // 1. Log in as Super Admin
        $superAdmin = \App\Models\User::factory()->create(['email' => 'admin@admin.com']);
        $superAdminRole = \Spatie\Permission\Models\Role::findOrCreate('Super Admin', 'web');
        $superAdmin->assignRole($superAdminRole);

        $response = $this->actingAs($superAdmin, 'admin')
            ->get('/admin/franchise-report');
        $response->assertStatus(200);

        // 2. Log in as Franchisor Inbound
        $inboundUser = \App\Models\User::where('email', 'franchisor.inbound@dchs.com')->first();
        $response = $this->actingAs($inboundUser, 'web')
            ->get('/franchisor');
        $response->assertStatus(200);

        $response = $this->actingAs($inboundUser, 'web')
            ->get('/franchisor/franchisor-admissions');
        $response->assertStatus(200);

        $response = $this->actingAs($inboundUser, 'web')
            ->get('/franchisor/franchisor-payments');
        $response->assertStatus(200);
    }
}
