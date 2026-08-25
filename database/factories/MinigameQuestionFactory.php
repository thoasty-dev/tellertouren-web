<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\MinigameQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MinigameQuestion>
 */
class MinigameQuestionFactory extends Factory
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
            'question_text' => fake()->sentence(),
            'guess_question_text' => 'Was denkt :user darüber?',
            'comment' => fake()->sentence(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
