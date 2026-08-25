<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\MinigameSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MinigameSession>
 */
class MinigameSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'secret' => MinigameSession::generateSecret(),
            'creator_name' => fake()->firstName(),
            'completed_at' => null,
        ];
    }
}
