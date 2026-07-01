<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response = $this->get('/campuses');
        $response->assertStatus(200);

        $response = $this->get('/courses');
        $response->assertStatus(200);

        $response = $this->get('/apply');
        $response->assertStatus(200);

        $response = $this->get('/contact');
        $response->assertStatus(200);
    }
}
