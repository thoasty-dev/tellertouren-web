<?php

namespace App\Poll;

use App\Models\PollAnswer;
use App\Models\PollQuestion;
use App\Models\PollVote;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CastPollVote
{
    public function handle(
        int $categoryId,
        int $questionId,
        int $answerId,
        ?string $ipAddress,
        string $cookieToken,
    ): bool {
        try {
            return DB::transaction(function () use ($categoryId, $questionId, $answerId, $ipAddress, $cookieToken): bool {
                $question = PollQuestion::query()->lockForUpdate()->find($questionId);
                $answer = PollAnswer::query()->find($answerId);

                if ($question === null || ! $question->is_active || $question->category_id !== $categoryId) {
                    throw ValidationException::withMessages(['question' => 'Diese Frage ist für die ausgewählte Umfrage ungültig.']);
                }

                if ($answer === null || $answer->poll_question_id !== $question->id) {
                    throw ValidationException::withMessages(['answer' => 'Diese Antwort gehört nicht zur ausgewählten Frage.']);
                }

                $alreadyVoted = PollVote::query()
                    ->forQuestion($questionId)
                    ->byIpOrCookie($ipAddress, $cookieToken)
                    ->exists();

                if ($alreadyVoted) {
                    return false;
                }

                PollVote::query()->create([
                    'poll_question_id' => $questionId,
                    'poll_answer_id' => $answerId,
                    'ip_address' => $ipAddress,
                    'cookie_token' => $cookieToken,
                ]);

                return true;
            });
        } catch (UniqueConstraintViolationException) {
            return false;
        }
    }
}
