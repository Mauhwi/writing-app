<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CommentThread extends Model
{
    protected $fillable = [
        'chapter_id',
        'created_by',
        'resolved',
    ];

    protected static function booted(): void
    {
        static::creating(function ($thread) {
            if (empty($thread->anchor)) {
                $thread->anchor = (string) Str::ulid();
            }
        });
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(CommentMessage::class, 'thread_id');
    }
}