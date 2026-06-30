<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentMessageDeleted implements ShouldBroadcast
{
    public function __construct(
        public $threadId,
        public $messageId,
        public $threadDeleted,
        public $chapterId,
        public $threadIdDeleted = null,
        public $anchor = null,
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('chapter.' . $this->chapterId);
    }

    public function broadcastAs()
    {
        return 'comment.message.deleted';
    }

    public function broadcastWith()
    {
        return [
            'threadId' => $this->threadId,
            'messageId' => $this->messageId,
            'threadDeleted' => $this->threadDeleted,
            'threadIdDeleted' => $this->threadIdDeleted,
            'anchor' => $this->anchor,
        ];
    }
}