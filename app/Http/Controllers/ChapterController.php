<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Chapter;

class ChapterController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
        ]);

        //Find the newest part in this specific project
        $latestPart = $project->parts()->latest()->first();

        if (!$latestPart) {
            // Create a default first part
            $latestPart = $project->parts()->create([
                'title' => 'Part 1',
                'summary' => 'Default first part',
            ]);
        }
        
        $project->chapters()->create([
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'part_id' => $latestPart->id,
        ]);

        return back()->with('success', 'Chapter created successfully.');
    }
}