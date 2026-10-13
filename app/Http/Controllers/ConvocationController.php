<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Campus;
use App\Models\ConvocationRegistration;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConvocationController extends Controller
{
    public function create(): View
    {
        $campuses = Campus::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM(name)) <> ?', ['main campus'])
            ->orderBy('city')
            ->get();

        $sessions = AcademicSession::query()
            ->where(function ($query) use ($campuses) {
                $query->whereNull('campus_id')
                    ->orWhereIn('campus_id', $campuses->pluck('id'));
            })
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get(['id', 'campus_id', 'name', 'is_active']);

        $courses = Course::query()->where('is_active', true)->orderBy('name')->get();

        return view('pages.convocation', compact('campuses', 'sessions', 'courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'integer', Rule::exists('campuses', 'id')->where('is_active', true)],
            'academic_session_id' => [
                'required',
                'integer',
                Rule::exists('academic_sessions', 'id')->where(
                    fn ($query) => $query->where(function ($sessionQuery) use ($request) {
                        $sessionQuery->whereNull('campus_id')
                            ->orWhere('campus_id', $request->integer('campus_id'));
                    }),
                ),
            ],
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')->where('is_active', true)],
            'student_name' => ['required', 'string', 'max:255'],
            'attendee_name' => ['required', 'string', 'max:255'],
            'attendee_relation' => ['required', Rule::in(['self', 'father', 'mother', 'brother', 'sister', 'spouse', 'guardian', 'other'])],
            'attendee_cnic' => ['required', 'regex:/^\d{5}-\d{7}-\d$/'],
        ], [
            'academic_session_id.exists' => 'The selected session does not belong to the selected campus.',
            'attendee_cnic.regex' => 'Enter the attendee CNIC in 00000-0000000-0 format.',
        ]);

        ConvocationRegistration::create($validated);

        return to_route('convocation.create')->with('success', 'Convocation information submitted successfully. Your entry has been sent to the selected campus.');
    }
}
