<?php

namespace App\Http\Controllers;

use App\Models\CommentThread;
use App\Models\CommentThreadRead;

class CommentThreadReadController extends Controller
{
    public function store(CommentThread $thread)
    {
        $latestMessageId = $thread
            ->messages()
            ->max('id');

        CommentThreadRead::updateOrCreate(
            [
                'thread_id' => $thread->id,
                'user_id' => auth()->id(),
            ],
            [
                'last_seen_message_id' => $latestMessageId,
            ]
        );

        return response()->json([
            'success' => true,
        ]);
    }
}