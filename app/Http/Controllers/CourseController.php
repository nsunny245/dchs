<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('is_active', true)->get();
        return view('courses.index', compact('courses'));
    }

    public function show($code)
    {
        $course = Course::where('code', $code)->firstOrFail();
        return view('courses.show', compact('course'));
    }
}
