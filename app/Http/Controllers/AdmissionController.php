<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeVoucher;
use App\Models\StudentFeeSnapshot;
use App\Models\VisitorQuery;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    public function index()
    {
        return view('pages.admissions');
    }

    public function apply()
    {
        $courses = Course::where('is_active', true)->orderBy('name')->get();
        $campuses = Campus::where('is_active', true)->orderBy('city')->get();

        return view('pages.apply', compact('courses', 'campuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'cnic' => 'required|string|max:20|unique:visitor_queries,cnic',
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

        VisitorQuery::create([
            'campus_id' => $campus->id,
            'visitor_name' => $validated['name'],
            'phone' => $validated['phone'],
            'relation_to_student' => 'self',
            'came_by' => 'website',
            'desired_course_id' => $course->id,
            'status' => 'new',
            'notes' => 'Online Inquiry submitted from website.',
            'father_name' => $validated['father_name'],
            'cnic' => $validated['cnic'],
            'dob' => $validated['dob'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'previous_education' => "Matric: Board - {$validated['metric_board']}, Marks - {$validated['metric_marks']}",
        ]);

        return redirect()->back()->with('success', 'Your online admission inquiry has been submitted successfully! The campus administration will contact you shortly to verify your details.');
    }

    public function complete(Admission $admission)
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasRole('Super Admin') || $user->campus_id === $admission->campus_id), 403);

        $admission->load(['student', 'campus', 'course', 'academicSession']);
        $vouchers = FeeVoucher::where('admission_id', $admission->id)->orderBy('sequence_no')->get();
        $feeSnapshot = $admission->student
            ? StudentFeeSnapshot::where('student_id', $admission->student->id)->with('feeStructure')->first()
            : null;

        return view('admissions.complete', compact('admission', 'vouchers', 'feeSnapshot'));
    }
}
