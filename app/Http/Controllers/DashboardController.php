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

        foreach ($sharedProjects as $sharedProject) {
            $sharedProject->word_count = $sharedProject->chapters->sum('word_count');
        }

        $canEdit = false;
        if (auth()->user()->role === 'author') 
        {
            $canEdit = true;
        }

        return inertia('Dashboard', [
            'projects' => $projects,
            'sharedProjects' => $sharedProjects,
            'canEdit' => $canEdit
        ]);
    }
}
