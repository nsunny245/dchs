<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use Illuminate\Http\Request;

class CampusController extends Controller
{
    public function index()
    {
        $campuses = Campus::where('is_active', true)->get();
        return view('campuses.index', compact('campuses'));
    }
}
