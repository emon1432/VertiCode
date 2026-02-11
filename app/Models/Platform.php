<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'display_name',
        'base_url',
        'image',
        'status',
        'last_contest_sync_at',
        'last_problem_sync_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_contest_sync_at' => 'datetime',
        'last_problem_sync_at' => 'datetime',
    ];

    /**
     * Get the platform profiles for the platform.
     */
    public function platformProfiles(): HasMany
    {
        return $this->hasMany(PlatformProfile::class);
    }

    /**
     * Get the contests for the platform.
     */
    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class);
    }

    /**
     * Get the problems for the platform.
     */
    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class);
    }

    /**
     * Scope a query to only include active platforms.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Check if contests need syncing.
     */
    public function needsContestSync(): bool
    {
        return $this->last_contest_sync_at === null ||
               $this->last_contest_sync_at->diffInHours(now()) >= 1;
    }

    /**
     * Check if problems need syncing.
     */
    public function needsProblemSync(): bool
    {
        return $this->last_problem_sync_at === null ||
               $this->last_problem_sync_at->diffInHours(now()) >= 1;
    }

    /**
     * Mark contests as synced.
     */
    public function markContestsSynced(int $count = 0): void
    {
        $this->update([
            'last_contest_sync_at' => now(),
        ]);
    }

    /**
     * Mark problems as synced.
     */
    public function markProblemsSynced(int $count = 0): void
    {
        $this->update([
            'last_problem_sync_at' => now(),
        ]);
    }
}
