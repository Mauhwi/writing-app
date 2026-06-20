<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'title',
        'summary',
        'project_id',
        'part_id',
        'order',
        'content',
        'word_count'
    ];

    protected $casts = [
        'content' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Chapter $chapter) {
            // Automatically get the max 'order' for THIS project and add 1
            if (is_null($chapter->order)) {
                $maxOrder = static::where('project_id', $chapter->project_id)->max('order');
                
                // If it's the first chapter, start at 1. Otherwise, increment by 1.
                $chapter->order = $maxOrder ? $maxOrder + 1 : 1;
            }
        });
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function part() {
        return $this->belongsTo(Part::class);
    }

    public function commentThreads()
    {
        return $this->hasMany(CommentThread::class);
    }

    public function comments() {
        return $this->hasMany(CommentMessage::class);
    }

    public static function extractText(array $node): string
    {
        $text = '';

        if (($node['type'] ?? null) === 'text') {
            $text .= $node['text'] ?? '';
        }

        foreach ($node['content'] ?? [] as $child) {
            $text .= self::extractText($child) . ' ';
        }

        return $text;
    }

    public static function calculateWordCount(?array $content): int
    {
        if (!$content) {
            return 0;
        }

        $text = self::extractText($content);

        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);
                
        preg_match_all('/\S+/u', $text, $matches);

        return count($matches[0]);
    }
}
