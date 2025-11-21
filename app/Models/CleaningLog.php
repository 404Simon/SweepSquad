<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class CleaningLog extends Model
{
    /** @use HasFactory<\Database\Factories\CleaningLogFactory> */
    use HasFactory;

    protected $fillable = [
        'cleaning_item_id',
        'user_id',
        'group_id',
        'dirtiness_at_clean',
        'coins_earned',
        'notes',
        'photo',
        'cleaned_at',
    ];

    /**
     * Get the cleaning item for this log.
     */
    public function cleaningItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CleaningItem::class);
    }

    /**
     * Get the user who performed this cleaning.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the group for this cleaning log.
     */
    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Scope a query to only include recent logs.
     */
    public function scopeRecent($query): void
    {
        $query->orderBy('cleaned_at', 'desc');
    }

    /**
     * Scope a query to only include logs for a specific user.
     */
    public function scopeForUser($query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include logs for a specific item.
     */
    public function scopeForItem($query, int $itemId): void
    {
        $query->where('cleaning_item_id', $itemId);
    }

    /**
     * Scope a query to only include logs for a specific group.
     */
    public function scopeForGroup($query, int $groupId): void
    {
        $query->where('group_id', $groupId);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'cleaned_at' => 'datetime',
        ];
    }
}
