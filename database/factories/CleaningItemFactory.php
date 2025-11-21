<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CleaningItem>
 */
final class CleaningItemFactory extends Factory
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
            'parent_id' => null,
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'cleaning_frequency_hours' => fake()->optional()->randomElement([24, 48, 72, 168]),
            'base_coin_reward' => fake()->numberBetween(0, 200),
            'last_cleaned_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'last_cleaned_by' => null,
            'order' => 0,
        ];
    }
}
