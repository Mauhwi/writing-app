<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $fillable = [
        'title',
        'summary',
    ];

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function chapters() {
        return $this->hasMany(Chapter::class);
    }
}
