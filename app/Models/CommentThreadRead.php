<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentThreadRead extends Model
{
    protected $fillable = [
        'thread_id',
        'user_id',
        'last_seen_message_id',
    ];

    public function thread()
    {
        return $this->belongsTo(CommentThread::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lastSeenMessage()
    {
        return $this->belongsTo(
            CommentMessage::class,
            'last_seen_message_id'
        );
    }
}