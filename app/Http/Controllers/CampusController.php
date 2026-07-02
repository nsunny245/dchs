<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use Illuminate\Http\Request;

class CampusController extends Controller
{
    public function index()
    {
        $campuses = Campus::where('is_active', true)->get();
        return view('pages.campuses.index', compact('campuses'));
    }

    public function show($id)
    {
        $campus = Campus::findOrFail($id);
        $courses = \App\Models\Course::where('is_active', true)->get();
        return view('pages.campuses.show', compact('campus', 'courses'));
    }
}
