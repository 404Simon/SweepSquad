<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

final class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'total_coins',
        'current_streak',
        'longest_streak',
        'last_cleaned_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the groups this user owns.
     */
    public function ownedGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    /**
     * Get the groups this user is a member of.
     */
    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get the group memberships for this user.
     */
    public function groupMemberships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Get the cleaning logs for this user.
     */
    public function cleaningLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CleaningLog::class);
    }

    /**
     * Get the achievements for this user.
     */
    public function achievements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Get the group invites created by this user.
     */
    public function createdInvites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupInvite::class, 'created_by');
    }

    /**
     * Add coins to the user's total.
     */
    public function addCoins(int $amount): void
    {
        $this->increment('total_coins', $amount);
    }

    /**
     * Update the user's cleaning streak.
     */
    public function updateStreak(): void
    {
        $now = now();
        $lastCleaned = $this->last_cleaned_at;

        if ($lastCleaned === null) {
            // First cleaning ever
            $this->current_streak = 1;
        } elseif ($lastCleaned->isToday()) {
            // Already cleaned today, no change
            return;
        } elseif ($lastCleaned->isYesterday()) {
            // Cleaned yesterday, increment streak
            $this->increment('current_streak');
        } else {
            // Streak broken, reset to 1
            $this->current_streak = 1;
        }

        // Update longest streak if current streak is now higher
        if ($this->current_streak > $this->longest_streak) {
            $this->longest_streak = $this->current_streak;
        }

        $this->last_cleaned_at = $now;
        $this->save();
    }

    /**
     * Reset the user's cleaning streak.
     */
    public function resetStreak(): void
    {
        $this->update([
            'current_streak' => 0,
        ]);
    }

    /**
     * Check if the user has earned a specific achievement.
     */
    public function hasAchievement(\App\Achievement $achievement): bool
    {
        return $this->achievements()
            ->where('achievement_code', $achievement->value)
            ->exists();
    }

    /**
     * Get all earned achievement codes.
     */
    public function earnedAchievements(): array
    {
        return $this->achievements()
            ->pluck('achievement_code')
            ->all();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'total_coins' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_cleaned_at' => 'datetime',
        ];
    }
}
