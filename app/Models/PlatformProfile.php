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
        'raw',
        'status',
        'last_synced_at',
    ];


    protected $casts = [
        'raw' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function getRankingAttribute(): mixed
    {
        if (! is_array($this->raw)) {
            return null;
        }

        $ranking = data_get($this->raw, 'ranking')
            ?? data_get($this->raw, 'rank_by_solved')
            ?? data_get($this->raw, 'rank_by_rating')
            ?? data_get($this->raw, 'global_rank')
            ?? data_get($this->raw, 'rank')
            ?? data_get($this->raw, 'profile.ranking')
            ?? data_get($this->raw, 'contest_global_ranking')
            ?? data_get($this->raw, 'profile_metrics.global_rank')
            ?? data_get($this->raw, 'profile.rank');

        if (is_string($ranking)) {
            $normalized = trim($ranking);

            if (preg_match('/^(\d+)(st|nd|rd|th)$/i', $normalized, $matches)) {
                return (int) $matches[1];
            }
        }

        return $ranking;
    }
}
