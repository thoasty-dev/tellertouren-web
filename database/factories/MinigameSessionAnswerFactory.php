<?php

namespace Database\Factories;

use App\Models\MinigameAnswer;
use App\Models\MinigameQuestion;
use App\Models\MinigameSession;
use App\Models\MinigameSessionAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MinigameSessionAnswer>
 */
class MinigameSessionAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'minigame_session_id' => MinigameSession::factory(),
            'minigame_question_id' => function (array $attributes): int {
                $categoryId = MinigameSession::query()->findOrFail($attributes['minigame_session_id'])->category_id;

                return MinigameQuestion::factory()->create(['category_id' => $categoryId])->id;
            },
            'minigame_answer_id' => function (array $attributes): int {
                return MinigameAnswer::factory()->create([
                    'minigame_question_id' => $attributes['minigame_question_id'],
                ])->id;
            },
        ];
    }
}
