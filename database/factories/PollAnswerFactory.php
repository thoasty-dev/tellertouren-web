<?php

namespace Database\Factories;

use App\Models\PollAnswer;
use App\Models\PollQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PollAnswer>
 */
class PollAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'poll_question_id' => PollQuestion::factory(),
            'answer_text' => fake()->sentence(),
            'sort_order' => 0,
        ];
    }
}
