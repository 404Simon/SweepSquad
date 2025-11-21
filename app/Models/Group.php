<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class Group extends Model
{
    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'owner_id',
        'settings',
    ];

    /**
     * Get the owner of the group.
     */
    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the members of the group.
     */
    public function members(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get the group memberships.
     */
    public function groupMemberships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Get the invites for the group.
     */
    public function invites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupInvite::class);
    }

    /**
     * Get the cleaning items for the group.
     */
    public function cleaningItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CleaningItem::class);
    }

    /**
     * Get the cleaning logs for the group.
     */
    public function cleaningLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CleaningLog::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (Group $group) {
            if (! $group->uuid) {
                $group->uuid = Str::uuid();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }
}
