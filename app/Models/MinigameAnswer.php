<?php

namespace App\Models;

use Database\Factories\MinigameAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinigameAnswer extends Model
{
    /** @use HasFactory<MinigameAnswerFactory> */
    use HasFactory;

    protected $fillable = ['minigame_question_id', 'answer_text', 'guess_answer_text', 'sort_order'];

    protected $attributes = ['sort_order' => 0];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(MinigameQuestion::class, 'minigame_question_id');
    }

    public function guessAnswerFor(string $userName): string
    {
        return str_replace(':user', $userName, $this->guess_answer_text);
    }
}
