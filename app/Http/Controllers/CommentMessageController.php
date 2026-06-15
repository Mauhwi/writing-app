<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommentThread;
use App\Models\CommentMessage;

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

        return response()->json($message);
    }

    public function destroy(CommentMessage $message)
    {
        $thread = $message->thread;
        $anchor = $thread->anchor;

        $message->delete();

        if ($thread->messages()->count() === 0) {
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