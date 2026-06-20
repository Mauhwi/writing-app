<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommentThread;
use App\Models\CommentMessage;
use App\Events\CommentMessageCreated;
use App\Events\CommentMessageDeleted;

class CommentMessageController extends Controller
{
    public function store(
        Request $request,
        CommentThread $thread
    ) {
        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $message = $thread->messages()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        $message->load('user');

        event(new CommentMessageCreated($thread->id, $message));

        return response()->json($message);
    }

    public function destroy(CommentMessage $message)
    {
        $thread = $message->thread;
        $anchor = $thread->anchor;

        $message->delete();

        $threadDeleted = $thread->messages()->count() === 0;

        event(new CommentMessageDeleted(
            $thread->id,
            $message->id,
            $threadDeleted,
            $threadDeleted ? $thread->id : null,
            $threadDeleted ? $anchor : null,
        ));

        if ($threadDeleted) {
            $thread->delete();

            return response()->json([
                'threadDeleted' => true,
                'threadId' => $thread->id,
                'anchor' => $anchor,
            ]);
        }

        return response()->json([
            'threadDeleted' => false,
        ]);
    }
}