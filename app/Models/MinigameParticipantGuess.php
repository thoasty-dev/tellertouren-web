<?php

namespace App\Models;

use Database\Factories\MinigameParticipantGuessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinigameParticipantGuess extends Model
{
    /** @use HasFactory<MinigameParticipantGuessFactory> */
    use HasFactory;

    protected $fillable = ['minigame_participant_id', 'minigame_question_id', 'minigame_answer_id', 'is_correct'];

    protected $attributes = ['is_correct' => false];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(MinigameParticipant::class, 'minigame_participant_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(MinigameQuestion::class, 'minigame_question_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(MinigameAnswer::class, 'minigame_answer_id');
    }
}
