<?php

namespace App\Models;

use App\Enums\Difficulty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Problem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'platform_id',
        'contest_id',
        'platform_problem_id',
        'slug',
        'name',
        'code',
        'description',
        'difficulty',
        'rating',
        'points',
        'accuracy',
        'time_limit_ms',
        'memory_limit_mb',
        'total_submissions',
        'accepted_submissions',
        'solved_count',
        'tags',
        'topics',
        'url',
        'editorial_url',
        'raw',
        'status',
        'is_premium',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'points' => 'float',
        'accuracy' => 'float',
        'tags' => 'array',
        'topics' => 'array',
        'raw' => 'array',
        'is_premium' => 'boolean',
        'difficulty' => Difficulty::class,
    ];

    /**
     * Get the platform that owns the problem.
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * Get the contest that owns the problem.
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * Scope a query to only include active problems.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include problems with a specific difficulty.
     */
    public function scopeByDifficulty($query, Difficulty|string $difficulty)
    {
        return $query->where('difficulty', $difficulty instanceof Difficulty ? $difficulty->value : $difficulty);
    }

    /**
     * Scope a query to only include problems within a rating range.
     */
    public function scopeByRatingRange($query, ?int $min = null, ?int $max = null)
    {
        if ($min !== null) {
            $query->where('rating', '>=', $min);
        }
        if ($max !== null) {
            $query->where('rating', '<=', $max);
        }
        return $query;
    }

    /**
     * Scope a query to only include free problems.
     */
    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Scope a query to only include premium problems.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope a query to filter by tags.
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope a query to filter by multiple tags.
     */
    public function scopeWithAnyTag($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Scope a query to order by difficulty rating.
     */
    public function scopeOrderByDifficulty($query, string $direction = 'asc')
    {
        return $query->orderBy('rating', $direction);
    }

    /**
     * Scope a query to order by popularity (solved count).
     */
    public function scopePopular($query)
    {
        return $query->orderBy('solved_count', 'desc');
    }

    /**
     * Calculate the acceptance rate percentage.
     */
    public function getAcceptanceRate(): float
    {
        if ($this->total_submissions === 0) {
            return 0.0;
        }

        return round(($this->accepted_submissions / $this->total_submissions) * 100, 2);
    }

    /**
     * Check if the problem is easy.
     */
    public function isEasy(): bool
    {
        return $this->difficulty === Difficulty::EASY;
    }

    /**
     * Check if the problem is medium.
     */
    public function isMedium(): bool
    {
        return $this->difficulty === Difficulty::MEDIUM;
    }

    /**
     * Check if the problem is hard.
     */
    public function isHard(): bool
    {
        return $this->difficulty === Difficulty::HARD;
    }

    /**
     * Get time limit in a human-readable format.
     */
    public function getFormattedTimeLimit(): ?string
    {
        if (!$this->time_limit_ms) {
            return null;
        }

        if ($this->time_limit_ms >= 1000) {
            return ($this->time_limit_ms / 1000) . 's';
        }

        return $this->time_limit_ms . 'ms';
    }

    /**
     * Get memory limit in a human-readable format.
     */
    public function getFormattedMemoryLimit(): ?string
    {
        if (!$this->memory_limit_mb) {
            return null;
        }

        return $this->memory_limit_mb . 'MB';
    }
}
