<?php

namespace Database\Factories;

use App\Models\MinigameAnswer;
use App\Models\MinigameParticipant;
use App\Models\MinigameParticipantGuess;
use App\Models\MinigameQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MinigameParticipantGuess>
 */
class MinigameParticipantGuessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'minigame_participant_id' => MinigameParticipant::factory(),
            'minigame_question_id' => function (array $attributes): int {
                $participant = MinigameParticipant::query()
                    ->with('session:id,category_id')
                    ->findOrFail($attributes['minigame_participant_id']);

                return MinigameQuestion::factory()->create([
                    'category_id' => $participant->session->category_id,
                ])->id;
            },
            'minigame_answer_id' => function (array $attributes): int {
                return MinigameAnswer::factory()->create([
                    'minigame_question_id' => $attributes['minigame_question_id'],
                ])->id;
            },
            'is_correct' => false,
        ];
    }
}
