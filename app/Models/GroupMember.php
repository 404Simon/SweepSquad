<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class GroupMember extends Model
{
    /** @use HasFactory<\Database\Factories\GroupMemberFactory> */
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'joined_at',
    ];

    /**
     * Get the group for this membership.
     */
    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user for this membership.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }
}
