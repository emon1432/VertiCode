<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformProfile extends Model
{
    protected $fillable = [
        'user_id',
        'platform_id',
        'handle',
        'rating',
        'total_solved',
        'profile_url',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];
}
