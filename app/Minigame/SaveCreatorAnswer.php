<?php

namespace App\Minigame;

use App\Models\MinigameAnswer;
use App\Models\MinigameQuestion;
use App\Models\MinigameSession;
use App\Models\MinigameSessionAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveCreatorAnswer
{
    public function handle(int $sessionId, int $categoryId, int $questionId, int $answerId): void
    {
        DB::transaction(function () use ($sessionId, $categoryId, $questionId, $answerId): void {
            $session = MinigameSession::query()->lockForUpdate()->find($sessionId);
            $question = MinigameQuestion::query()->find($questionId);
            $answer = MinigameAnswer::query()->find($answerId);

            if ($session === null || $session->category_id !== $categoryId || $session->isCompleted()) {
                throw ValidationException::withMessages(['session' => 'Diese Minigame-Sitzung kann nicht geändert werden.']);
            }

            if ($question === null || ! $question->is_active || $question->category_id !== $categoryId) {
                throw ValidationException::withMessages(['question' => 'Diese Frage gehört nicht zur Minigame-Kategorie.']);
            }

            if ($answer === null || $answer->minigame_question_id !== $questionId) {
                throw ValidationException::withMessages(['answer' => 'Diese Antwort gehört nicht zur ausgewählten Frage.']);
            }

            MinigameSessionAnswer::query()->updateOrCreate(
                ['minigame_session_id' => $sessionId, 'minigame_question_id' => $questionId],
                ['minigame_answer_id' => $answerId],
            );
        });
    }
}
