<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'title',
        'summary',
        'project_id',
        'part_id'
    ];

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
