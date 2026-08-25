<?php

namespace App\Minigame;

use App\Models\MinigameAnswer;
use App\Models\MinigameParticipant;
use App\Models\MinigameParticipantGuess;
use App\Models\MinigameQuestion;
use App\Models\MinigameSessionAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveParticipantGuess
{
    public function handle(int $participantId, int $sessionId, int $questionId, int $answerId, string $cookieToken): void
    {
        DB::transaction(function () use ($participantId, $sessionId, $questionId, $answerId, $cookieToken): void {
            $participant = MinigameParticipant::query()
                ->with('session')
                ->lockForUpdate()
                ->find($participantId);
            $question = MinigameQuestion::query()->find($questionId);
            $answer = MinigameAnswer::query()->find($answerId);

            if ($participant === null
                || $participant->minigame_session_id !== $sessionId
                || ! is_string($participant->cookie_token)
                || ! hash_equals($participant->cookie_token, $cookieToken)
                || $participant->isCompleted()) {
                throw ValidationException::withMessages(['participant' => 'Diese Teilnahme kann nicht geändert werden.']);
            }

            if (! $participant->session->isCompleted()) {
                throw ValidationException::withMessages(['session' => 'Das Minigame ist noch nicht freigegeben.']);
            }

            if ($question === null || ! $question->is_active || $question->category_id !== $participant->session->category_id) {
                throw ValidationException::withMessages(['question' => 'Diese Frage gehört nicht zur Minigame-Sitzung.']);
            }

            if ($answer === null || $answer->minigame_question_id !== $questionId) {
                throw ValidationException::withMessages(['answer' => 'Diese Antwort gehört nicht zur ausgewählten Frage.']);
            }

            $creatorAnswerId = MinigameSessionAnswer::query()
                ->where('minigame_session_id', $sessionId)
                ->where('minigame_question_id', $questionId)
                ->value('minigame_answer_id');

            if ($creatorAnswerId === null) {
                throw ValidationException::withMessages(['question' => 'Für diese Frage fehlt die Antwort des Erstellers.']);
            }

            MinigameParticipantGuess::query()->updateOrCreate(
                ['minigame_participant_id' => $participantId, 'minigame_question_id' => $questionId],
                ['minigame_answer_id' => $answerId, 'is_correct' => (int) $creatorAnswerId === $answerId],
            );
        });
    }
}
