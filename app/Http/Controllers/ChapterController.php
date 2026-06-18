<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Chapter;
use App\Events\ChapterContentUpdated;

class ChapterController extends Controller
{
    public function show(Request $request, Project $project, Chapter $chapter)
    {
        $this->authorize('view', $project);

        $chapter->updated_at = $chapter->updated_at->toISOString();
        
        $chapter->load([
            'commentThreads.messages.user',
        ]);

        $projectArray = [
            'id' => $project->id,
            'title' => $project->title,
            'parts' => $project->parts->map(function ($part) {
                return [
                    'id' => $part->id,
                    'title' => $part->title,
                    'chapters' => $part->chapters->sortBy('order')->map(function ($chapter) {
                        return [
                            'id' => $chapter->id,
                            'title' => $chapter->title,
                        ];
                    })->values()->toArray()
                ];
            })->toArray()
        ];

        // dd(
        //     $chapter->commentThreads->count(),
        //     $chapter->commentThreads
        // );

        return inertia('Chapters/Show', [
            'project' => $projectArray,
            'chapter' => $chapter,
            'canEdit' => $project->user_id === auth()->id(),
            'commentThreads' => $chapter->commentThreads,
        ]);
    }

    public function getContent(Project $project, Chapter $chapter)
    {
        $this->authorize('view', $project);

        return response()->json([
            'content' => $chapter->content,
            'updated_at' => $chapter->updated_at,
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
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                    ],
                ],
            ],

            'word_count' => 0,
        ]);

        return back()->with('success', 'Chapter created successfully.');
    }

    public function updateContent(Request $request, Project $project, Chapter $chapter)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'content' => ['nullable', 'array'],
        ]);
        
        $chapter->update([
            'content' => $validated['content'],
            'word_count' => Chapter::calculateWordCount($validated['content']),
        ]);

        broadcast(
            new ChapterContentUpdated(
                $chapter->id
            )
        )->toOthers();

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