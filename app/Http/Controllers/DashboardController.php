<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {        
        $projects = auth()->user()
            ->projects()
            ->with('chapters')
            ->get();

        $sharedProjects = auth()->user()
            ->sharedProjects()
            ->with('chapters')
            ->get();

        foreach ($projects as $project) {
            $project->word_count = $project->chapters->sum('word_count');
        }

        return inertia('Dashboard', [
            'projects' => $projects,
            'sharedProjects' => $sharedProjects
        ]);
    }
}
