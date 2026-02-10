<?php

namespace App\Models;

use App\Enums\ContestType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'platform_id',
        'platform_contest_id',
        'slug',
        'name',
        'description',
        'type',
        'phase',
        'duration_seconds',
        'start_time',
        'end_time',
        'url',
        'participant_count',
        'is_rated',
        'tags',
        'raw',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_rated' => 'boolean',
        'tags' => 'array',
        'raw' => 'array',
        'type' => ContestType::class,
    ];

    /**
     * Get the platform that owns the contest.
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * Get the problems for the contest.
     */
    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class);
    }

    /**
     * Scope a query to only include active contests.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include upcoming contests.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Scope a query to only include ongoing contests.
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_time', '<=', now())
            ->where('end_time', '>=', now());
    }

    /**
     * Scope a query to only include past contests.
     */
    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }

    /**
     * Scope a query to only include rated contests.
     */
    public function scopeRated($query)
    {
        return $query->where('is_rated', true);
    }

    /**
     * Scope a query to filter by contest type.
     */
    public function scopeOfType($query, ContestType|string $type)
    {
        return $query->where('type', $type instanceof ContestType ? $type->value : $type);
    }

    /**
     * Check if the contest is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time && $this->start_time->isFuture();
    }

    /**
     * Check if the contest is ongoing.
     */
    public function isOngoing(): bool
    {
        return $this->start_time && $this->end_time &&
            $this->start_time->isPast() && $this->end_time->isFuture();
    }

    /**
     * Check if the contest has ended.
     */
    public function hasEnded(): bool
    {
        return $this->end_time && $this->end_time->isPast();
    }

    /**
     * Get the duration in a human-readable format.
     */
    public function getFormattedDuration(): ?string
    {
        if (!$this->duration_seconds) {
            return null;
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        return "{$minutes}m";
    }
}
