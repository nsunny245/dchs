<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('is_active', true)->get();
        return view('pages.courses.index', compact('courses'));
    }

    public function show($code)
    {
        $course = Course::where('code', $code)->firstOrFail();
        return view('pages.courses.show', compact('course'));
    }
}
