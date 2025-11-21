<?php

declare(strict_types=1);

namespace App\Models;

use App\InviteType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class GroupInvite extends Model
{
    /** @use HasFactory<\Database\Factories\GroupInviteFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'code',
        'group_id',
        'created_by',
        'type',
        'expires_at',
        'used_by',
        'used_at',
    ];

    /**
     * Get the group for this invite.
     */
    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user who created this invite.
     */
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who used this invite.
     */
    public function usedByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Scope to get only valid invites.
     */
    public function scopeValid(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q->whereNull('used_at')
                ->where(function (Builder $subQuery): void {
                    $subQuery->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                });
        });
    }

    /**
     * Scope to get expired invites.
     */
    public function scopeExpired(Builder $query): void
    {
        $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope to get unused invites.
     */
    public function scopeUnused(Builder $query): void
    {
        $query->whereNull('used_at');
    }

    /**
     * Check if the invite is valid.
     */
    public function isValid(): bool
    {
        // Already used
        if ($this->used_at !== null) {
            return false;
        }

        // Expired
        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Mark the invite as used by a user.
     */
    public function markAsUsed(User $user): void
    {
        $this->update([
            'used_by' => $user->id,
            'used_at' => now(),
        ]);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (GroupInvite $invite): void {
            if (! $invite->uuid) {
                $invite->uuid = Str::uuid();
            }

            if (! $invite->code) {
                $invite->code = mb_strtoupper(Str::random(10));
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'type' => InviteType::class,
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
