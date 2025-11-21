<?php

declare(strict_types=1);

namespace App\Models;

use App\Achievement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class UserAchievement extends Model
{
    /** @use HasFactory<\Database\Factories\UserAchievementFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_code',
        'earned_at',
    ];

    /**
     * Get the user for this achievement.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Achievement enum instance.
     */
    public function achievement(): Achievement
    {
        return Achievement::from($this->achievement_code);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }
}
