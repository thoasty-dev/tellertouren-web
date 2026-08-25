<?php

namespace Database\Factories;

use App\Models\PollAnswer;
use App\Models\PollQuestion;
use App\Models\PollVote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PollVote>
 */
class PollVoteFactory extends Factory
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
            'poll_answer_id' => function (array $attributes): int {
                return PollAnswer::factory()->create([
                    'poll_question_id' => $attributes['poll_question_id'],
                ])->id;
            },
            'ip_address' => fake()->ipv4(),
            'cookie_token' => fake()->sha256(),
        ];
    }
}
