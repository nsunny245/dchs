<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Campus;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch active programs and campuses from database
        $programs = Course::where('is_active', true)->orderBy('name')->get();
        $campuses = Campus::where('is_active', true)->orderBy('city')->get();
        
        return view('pages.home', compact('programs', 'campuses'));
    }

    public function contact()
    {
        $campuses = Campus::where('is_active', true)->orderBy('city')->get();
        return view('pages.contact', compact('campuses'));
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        \App\Models\ContactSubmission::create($validated);

        return redirect()->back()->with('success', 'Thank you for contacting Daniyal Group of Colleges! Your query has been submitted and a campus advisor will reach out to you shortly.');
    }

    public function chairmansMessage()
    {
        return view('pages.about.chairmans-message');
    }

    public function visionMission()
    {
        return view('pages.about.vision-mission');
    }

    public function accreditation()
    {
        return view('pages.about.accreditation');
    }

    public function leadership()
    {
        return view('pages.about.leadership');
    }
}
