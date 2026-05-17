<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'description',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function chapters() {
        return $this->hasMany(Chapter::class)->orderBy('order');
    }

    public function parts() {
        return $this->hasMany(Part::class)->orderBy('order');
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}
