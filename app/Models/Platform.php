<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'base_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function platformProfiles()
    {
        return $this->hasMany(PlatformProfile::class);
    }
}
