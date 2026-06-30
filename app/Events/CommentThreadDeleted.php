<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommentThreadDeleted implements ShouldBroadcast
{
    public function __construct(
        public $threadId,
        public $chapterId,
        public $anchor
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('chapter.' . $this->chapterId);
    }

    public function broadcastAs()
    {
        return 'comment.thread.deleted';
    }

    public function broadcastWith()
    {
        return [
            'threadId' => $this->threadId,
            'anchor' => $this->anchor,
        ];
    }
}