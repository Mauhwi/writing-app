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
            'commentThreads.messages',
            'commentThreads.reads',
        ]);

        $unreadThreadIds = $chapter->commentThreads
            ->filter(function ($thread) {
                $latestMessage = $thread->messages->sortBy('id')->last();

                if (! $latestMessage || $latestMessage->user_id === auth()->id()) {
                    return false;
                }

                $read = $thread->reads
                    ->firstWhere('user_id', auth()->id());

                return ! $read
                    || $read->last_seen_message_id < $latestMessage->id;
            })
            ->pluck('id')
            ->values();

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
            'commentThreads' => $chapter->commentThreads->map(function ($thread) {
                $userId = auth()->id();
                $firstMessageUserId = $thread->messages->first()?->user_id;
                
                return array_merge($thread->toArray(), [
                    'canDeleteThread' => $firstMessageUserId === $userId,
                    'messages' => $thread->messages->map(function ($message) use ($userId) {
                        return array_merge($message->toArray(), [
                            'canDelete' => $message->user_id === $userId,
                        ]);
                    })->toArray(),
                ]);
            }),
            'unreadThreadIds' => $unreadThreadIds,
            'unreadThreads' => $unreadThreadIds->count(),
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

    public function updateCommentMarks(Request $request, Project $project, Chapter $chapter)
    {
        $this->authorize('view', $project);

        $validated = $request->validate([
            'content' => 'required|array',
        ]);

        // Strip comment marks from both, then compare
        $incoming = $this->stripCommentMarks($validated['content']);
        $stored   = $this->stripCommentMarks($chapter->content);

        //dd($incoming, $stored);

        if (json_encode($this->normalizeForComparison($incoming)) !== json_encode($this->normalizeForComparison($stored))) {
            abort(403, 'Only comment mark changes are permitted.');
        }

        $chapter->update(['content' => $validated['content']]);

        return response()->json(['ok' => true]);
    }

    private function stripCommentMarks(array $node): array
    {
        if (isset($node['marks'])) {
            $node['marks'] = array_values(
                array_filter(
                    $node['marks'],
                    fn($mark) => $mark['type'] !== 'comment'
                )
            );

            if (empty($node['marks'])) {
                unset($node['marks']);
            }
        }

        if (isset($node['content'])) {
            $node['content'] = array_map(
                fn($child) => $this->stripCommentMarks($child),
                $node['content']
            );

            $node['content'] = $this->mergeAdjacentTextNodes($node['content']);
        }

        return $node;
    }

    private function mergeAdjacentTextNodes(array $nodes): array
    {
        $merged = [];

        foreach ($nodes as $node) {
            $last = end($merged);

            if (
                $last &&
                $last['type'] === 'text' &&
                $node['type'] === 'text' &&
                ($last['marks'] ?? []) === ($node['marks'] ?? [])
            ) {
                $merged[array_key_last($merged)]['text'] .= $node['text'];
            } else {
                $merged[] = $node;
            }
        }

        return array_values($merged);
    }

    private function normalizeForComparison(array $node): array
    {
        ksort($node);

        if (isset($node['content'])) {
            $node['content'] = array_map(
                fn($child) => $this->normalizeForComparison($child),
                $node['content']
            );
        }

        return $node;
    }
}