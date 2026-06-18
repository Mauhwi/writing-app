<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Chapter;
use App\Models\CommentThread;
use App\Events\CommentMessageCreated;
use App\Events\CommentMessageDeleted;

class CommentThreadController extends Controller
{
    public function store(
        Request $request,
        Project $project,
        Chapter $chapter
    ) {
        $this->authorize('view', $project);

        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $thread = CommentThread::create([
            'chapter_id' => $chapter->id,
            'created_by' => auth()->id(),
        ]);

        $message = $thread->messages()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        $thread->load('messages.user');

        event(new CommentMessageCreated($thread->id, $message));

        return response()->json([
            'anchor' => $thread->anchor,
            'thread' => $thread,
        ]);
    }

    public function destroy(CommentThread $thread)
    {
        $anchor = $thread->anchor;

        $thread->delete();

        return response()->json([
            'anchor' => $anchor,
        ]);
    }
}
