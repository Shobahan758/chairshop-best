<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['meta_pixel_id', 'google_tag_id', 'tiktok_pixel_id', 'pinterest_tag_id', 'snapchat_pixel_id', 'tracking_enabled'])]
class TrackingSetting extends Model
{
    protected function casts(): array
    {
        return ['tracking_enabled' => 'boolean'];
    }
}
