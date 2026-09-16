<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event', 'path', 'session_hash', 'metadata'])]
class TrackingEvent extends Model
{
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
