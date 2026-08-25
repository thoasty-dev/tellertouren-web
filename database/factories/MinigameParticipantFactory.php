<?php

namespace Database\Factories;

use App\Models\MinigameParticipant;
use App\Models\MinigameSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MinigameParticipant>
 */
class MinigameParticipantFactory extends Factory
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
            'name' => fake()->firstName(),
            'cookie_token' => fake()->sha256(),
            'completed_at' => null,
        ];
    }
}
