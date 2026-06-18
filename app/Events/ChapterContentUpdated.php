<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChapterContentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $chapterId
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('chapter.' . $this->chapterId);
    }

    public function broadcastAs()
    {
        return 'chapter.content.updated';
    }

    public function broadcastWith()
    {
        return [
            'chapterId' => $this->chapterId,
        ];
    }
}