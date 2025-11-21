<?php

declare(strict_types=1);

namespace App\Models;

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
        return $this->hasMany(self::class, 'parent_id');
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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'last_cleaned_at' => 'datetime',
        ];
    }
}
