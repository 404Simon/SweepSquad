<?php

declare(strict_types=1);

namespace Database\Factories;

use App\InviteType;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupInvite>
 */
final class GroupInviteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'created_by' => User::factory(),
            'type' => fake()->randomElement([
                InviteType::Permanent,
                InviteType::SingleUse,
                InviteType::TimeLimited,
            ]),
            'expires_at' => null,
            'used_by' => null,
            'used_at' => null,
        ];
    }

    /**
     * Indicate the invite is permanent.
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => InviteType::Permanent,
            'expires_at' => null,
        ]);
    }

    /**
     * Indicate the invite is single use.
     */
    public function singleUse(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => InviteType::SingleUse,
            'expires_at' => null,
        ]);
    }

    /**
     * Indicate the invite is time limited.
     */
    public function timeLimited(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => InviteType::TimeLimited,
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Indicate the invite has been used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes): array => [
            'used_by' => User::factory(),
            'used_at' => now(),
        ]);
    }

    /**
     * Indicate the invite has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => InviteType::TimeLimited,
            'expires_at' => now()->subDay(),
        ]);
    }
}
