<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;

class AdmissionController extends Controller
{
    public function index()
    {
        return view('admissions.index');
    }

    public function apply()
    {
        return view('admissions.apply');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'cnic' => 'required|string|max:20|unique:admissions,cnic',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'program' => 'required|string|exists:courses,code',
            'campus' => 'required|string|exists:campuses,city',
            'metric_board' => 'required|string|max:255',
            'metric_marks' => 'required|numeric|min:0',
        ]);

        $campus = Campus::where('city', $validated['campus'])->firstOrFail();
        $course = Course::where('code', $validated['program'])->firstOrFail();

        Admission::create([
            'applicant_name' => $validated['name'],
            'father_name' => $validated['father_name'],
            'cnic' => $validated['cnic'],
            'dob' => $validated['dob'],
            'gender' => $validated['gender'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'previous_education' => "Matric: Board - {$validated['metric_board']}, Marks - {$validated['metric_marks']}",
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Your online admission application has been submitted successfully! Please visit your campus with original documents for verification.');
    }
}
