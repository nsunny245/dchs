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
        
        return view('home', compact('programs', 'campuses'));
    }

    public function contact()
    {
        return view('contact');
    }
}
