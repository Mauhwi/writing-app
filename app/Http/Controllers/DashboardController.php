<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        $projects = Cache::remember(
            "user:{$user->id}:projects",
            86400,
            fn () => $user
                ->projects()
                ->with('chapters')
                ->get()
        );

        $sharedProjects = Cache::remember(
            "user:{$user->id}:shared-projects",
            86400,
            fn () => $user
                ->sharedProjects()
                ->with('chapters')
                ->get()
        );

        
        foreach ($projects as $project) {
            $project->word_count = $project->chapters->sum('word_count');
        }

        foreach ($sharedProjects as $sharedProject) {
            $sharedProject->word_count = $sharedProject->chapters->sum('word_count');
        }

        $canEdit = $user->role === 'author';

        return inertia('Dashboard', [
            'projects' => $projects,
            'sharedProjects' => $sharedProjects,
            'canEdit' => $canEdit,
        ]);
    }
}
