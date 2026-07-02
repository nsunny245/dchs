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
}
