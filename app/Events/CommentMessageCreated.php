<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommentMessageCreated implements ShouldBroadcast
{
    public function __construct(
        public $threadId,
        public $message
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('chapter.' . $this->message->thread->chapter_id);
    }

    public function broadcastAs()
    {
        return 'comment.message.created';
    }

    public function broadcastWith()
    {
        return [
            'thread_id' => $this->threadId,
            'message' => $this->message,
        ];
    }
}