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
        'order'
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

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}
