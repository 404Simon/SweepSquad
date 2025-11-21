<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class GroupInvite extends Model
{
    /** @use HasFactory<\Database\Factories\GroupInviteFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
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

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (GroupInvite $invite) {
            if (! $invite->uuid) {
                $invite->uuid = Str::uuid();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
