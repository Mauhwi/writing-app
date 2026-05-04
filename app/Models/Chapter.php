<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
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
