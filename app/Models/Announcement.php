<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['title', 'message', 'type', 'event_at', 'created_by'];

    protected function casts(): array
    {
        return ['event_at' => 'datetime'];
    }
}