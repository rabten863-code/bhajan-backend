<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bhajan extends Model
{
    protected $fillable = ['title', 'lyrics', 'language', 'status', 'audio_path'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'bhajan_category');
    }
}