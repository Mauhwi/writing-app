<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentMessage extends Model
{
    protected $fillable = [
        'thread_id',
        'user_id',
        'body',
    ];

    public function thread()
    {
        return $this->belongsTo(CommentThread::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}