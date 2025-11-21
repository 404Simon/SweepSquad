<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class CleaningItem extends Model
{
    /** @use HasFactory<\Database\Factories\CleaningItemFactory> */
    use HasFactory;

    protected $fillable = [
        'group_id',
        'parent_id',
        'name',
        'description',
        'cleaning_frequency_hours',
        'base_coin_reward',
        'last_cleaned_at',
        'last_cleaned_by',
        'order',
    ];

    /**
     * Get the group for this cleaning item.
     */
    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the parent cleaning item (if this is a sub-item).
     */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child cleaning items.
     */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get the user who last cleaned this item.
     */
    public function lastCleanedByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'last_cleaned_by');
    }

    /**
     * Get the cleaning logs for this item.
     */
    public function cleaningLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CleaningLog::class);
    }

    /**
     * Calculate the dirtiness percentage based on time since last clean.
     */
    public function calculateDirtiness(): float
    {
        if ($this->cleaning_frequency_hours === null || $this->cleaning_frequency_hours <= 0) {
            return 0.0;
        }

        if ($this->last_cleaned_at === null) {
            return 100.0;
        }

        $hoursSinceLastClean = $this->last_cleaned_at->diffInHours(now());
        $dirtiness = ($hoursSinceLastClean / $this->cleaning_frequency_hours) * 100;

        return min($dirtiness, 100.0);
    }

    /**
     * Calculate coins available if cleaned now.
     */
    public function getCoinsAvailable(): int
    {
        if ($this->cleaning_frequency_hours === null || $this->cleaning_frequency_hours <= 0) {
            return 0;
        }

        $dirtiness = $this->calculateDirtiness();

        // Bonus multiplier: 1.5x if overdue, 1.2x if needs attention
        $multiplier = match (true) {
            $dirtiness >= 100.0 => 1.5,
            $dirtiness >= 80.0 => 1.2,
            default => 1.0,
        };

        return (int) round($this->base_coin_reward * $multiplier);
    }

    /**
     * Scope a query to only include root items (no parent).
     */
    public function scopeRoots($query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include overdue items.
     */
    public function scopeOverdue($query): void
    {
        $query->whereNotNull('cleaning_frequency_hours')
            ->where('cleaning_frequency_hours', '>', 0)
            ->where(function ($q): void {
                $q->whereNull('last_cleaned_at')
                    ->orWhereRaw('(julianday(datetime("now")) - julianday(last_cleaned_at)) * 24 >= cleaning_frequency_hours');
            });
    }

    /**
     * Scope a query to only include items that need attention.
     */
    public function scopeNeedsAttention($query): void
    {
        $query->whereNotNull('cleaning_frequency_hours')
            ->where('cleaning_frequency_hours', '>', 0)
            ->where(function ($q): void {
                $q->whereNull('last_cleaned_at')
                    ->orWhereRaw('(julianday(datetime("now")) - julianday(last_cleaned_at)) * 24 >= cleaning_frequency_hours * 0.8');
            });
    }

    /**
     * Scope a query to only include clean items.
     */
    public function scopeFreshlyClean($query): void
    {
        $query->whereNotNull('cleaning_frequency_hours')
            ->where('cleaning_frequency_hours', '>', 0)
            ->whereNotNull('last_cleaned_at')
            ->whereRaw('(julianday(datetime("now")) - julianday(last_cleaned_at)) * 24 < cleaning_frequency_hours * 0.2');
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'last_cleaned_at' => 'datetime',
        ];
    }

    /**
     * Get the dirtiness percentage attribute.
     */
    protected function dirtinessPercentage(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->calculateDirtiness(),
        );
    }

    /**
     * Check if the item is overdue for cleaning.
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->dirtiness_percentage >= 100.0,
        );
    }

    /**
     * Check if the item needs attention.
     */
    protected function needsAttention(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->dirtiness_percentage >= 80.0,
        );
    }

    /**
     * Check if the item is freshly clean.
     */
    protected function isFreshlyClean(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->dirtiness_percentage < 20.0,
        );
    }

    /**
     * Get the coins available attribute.
     */
    protected function coinsAvailable(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->getCoinsAvailable(),
        );
    }
}
