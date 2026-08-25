<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\PollQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PollQuestion>
 */
class PollQuestionFactory extends Factory
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
            'is_active' => true,
        ];
    }
}
