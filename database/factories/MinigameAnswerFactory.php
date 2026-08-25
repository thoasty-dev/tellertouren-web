<?php

namespace Database\Factories;

use App\Models\MinigameAnswer;
use App\Models\MinigameQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MinigameAnswer>
 */
class MinigameAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'minigame_question_id' => MinigameQuestion::factory(),
            'answer_text' => fake()->sentence(),
            'guess_answer_text' => ':user entscheidet sich dafür.',
            'sort_order' => 0,
        ];
    }
}
