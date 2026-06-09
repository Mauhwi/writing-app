<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Chapter;

class ChapterController extends Controller
{
    public function show(Request $request, Project $project, Chapter $chapter)
    {
        $this->authorize('view', $project);

        $chapter->updated_at = $chapter->updated_at->toISOString();
        
        if ($chapter->content) {
            $chapter->word_count = str_word_count($chapter->content);
        }
        
        return inertia('Chapters/Show', [
            'project' => [
                'id' => $project->id,
                'title' => $project->title,
            ],
            'chapter' => $chapter
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
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

    public function updateContent(Request $request, Project $project, Chapter $chapter)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'content' => ['nullable', 'string'],
        ]);

        $chapter->update($validated);

        return back();
    }

    public function updateDetails(Request $request, Project $project, Chapter $chapter)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
        ]);

        $chapter->update($validated);

        return back();
    }
}