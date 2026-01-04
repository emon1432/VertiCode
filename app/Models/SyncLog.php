<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'platform_profile_id',
        'status',
        'http_code',
        'error_message',
        'duration_ms',
    ];
}
