<?php

namespace App\Models;

use Database\Factories\MinigameSessionAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinigameSessionAnswer extends Model
{
    /** @use HasFactory<MinigameSessionAnswerFactory> */
    use HasFactory;

    protected $fillable = ['minigame_session_id', 'minigame_question_id', 'minigame_answer_id'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(MinigameSession::class, 'minigame_session_id');
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
