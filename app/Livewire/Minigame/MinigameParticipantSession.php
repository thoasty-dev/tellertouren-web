<?php

namespace App\Livewire\Minigame;

use App\Minigame\SaveParticipantGuess;
use App\Models\MinigameParticipant;
use App\Models\MinigameQuestion;
use App\Models\MinigameSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MinigameParticipantSession extends Component
{
    private const COOKIE_NAME = 'minigame_participant_token';

    private const COOKIE_DURATION = 60 * 24 * 30;

    #[Locked]
    public string $secret;

    #[Locked]
    public int $sessionId;

    #[Locked]
    public ?int $participantId = null;

    public string $participantName = '';

    /** @var array<int, int> */
    public array $selectedAnswers = [];

    public int $currentStep = 1;

    public int $currentQuestionIndex = 0;

    public function mount(string $secret): void
    {
        $session = MinigameSession::query()->bySecret($secret)->firstOrFail();
        abort_unless($session->isCompleted(), 404);

        $this->secret = $secret;
        $this->sessionId = $session->id;
        $this->loadParticipant();
    }

    public function render(): View
    {
        $session = MinigameSession::query()->with('category')->findOrFail($this->sessionId);
        $questions = $this->currentStep < 2
            ? collect()
            : MinigameQuestion::query()->active()->forCategory($session->category_id)->ordered()->with('answers')->get();
        $participant = $this->participantId === null
            ? null
            : MinigameParticipant::query()
                ->withCount(['guesses', 'guesses as correct_guesses_count' => fn (Builder $query): Builder => $query->where('is_correct', true)])
                ->findOrFail($this->participantId);
        $creatorAnswers = $this->currentStep === 3
            ? $session->sessionAnswers()->with('answer')->get()->keyBy('minigame_question_id')
            : collect();
        $participantGuesses = $this->currentStep === 3 && $participant !== null
            ? $participant->guesses()->with('answer')->get()->keyBy('minigame_question_id')
            : collect();

        return view('livewire.minigame.minigame-participant-session', [
            'session' => $session,
            'questions' => $questions,
            'participant' => $participant,
            'totalQuestions' => $questions->count(),
            'answeredCount' => count($this->selectedAnswers),
            'creatorAnswers' => $creatorAnswers,
            'participantGuesses' => $participantGuesses,
        ]);
    }

    public function startPlaying(): void
    {
        $validated = $this->validate([
            'participantName' => ['required', 'string', 'min:2', 'max:50'],
        ]);
        $token = $this->cookieToken();

        $participant = MinigameParticipant::query()->firstOrCreate(
            ['minigame_session_id' => $this->sessionId, 'cookie_token' => $token],
            ['name' => $validated['participantName']],
        );

        $this->participantId = $participant->id;
        $this->participantName = $participant->name;
        $this->currentStep = $participant->isCompleted() ? 3 : 2;
    }

    public function saveGuess(int $questionId, SaveParticipantGuess $saveGuess): void
    {
        if ($this->participantId === null || ! isset($this->selectedAnswers[$questionId])) {
            return;
        }

        $saveGuess->handle(
            participantId: $this->participantId,
            sessionId: $this->sessionId,
            questionId: $questionId,
            answerId: (int) $this->selectedAnswers[$questionId],
            cookieToken: $this->cookieToken(),
        );
    }

    public function nextQuestion(): void
    {
        $session = MinigameSession::query()->findOrFail($this->sessionId);
        $lastIndex = max(0, MinigameQuestion::query()->active()->forCategory($session->category_id)->count() - 1);
        $this->currentQuestionIndex = min($this->currentQuestionIndex + 1, $lastIndex);
    }

    public function previousQuestion(): void
    {
        $this->currentQuestionIndex = max(0, $this->currentQuestionIndex - 1);
    }

    public function completeQuestions(): void
    {
        if ($this->participantId === null) {
            return;
        }

        DB::transaction(function (): void {
            $participant = MinigameParticipant::query()->with('session')->lockForUpdate()->findOrFail($this->participantId);
            $token = $this->cookieToken();
            abort_unless(is_string($participant->cookie_token) && hash_equals($participant->cookie_token, $token), 404);
            abort_unless($participant->minigame_session_id === $this->sessionId, 404);

            $questionIds = MinigameQuestion::query()->active()->forCategory($participant->session->category_id)->pluck('id');
            $guessedIds = $participant->guesses()->whereIn('minigame_question_id', $questionIds)->pluck('minigame_question_id');

            if ($questionIds->diff($guessedIds)->isNotEmpty()) {
                $this->addError('questions', 'Bitte beantworte zuerst alle Fragen.');

                return;
            }

            $participant->update(['completed_at' => now()]);
            $this->currentStep = 3;
            $this->dispatch('minigame-completed');
        });
    }

    private function loadParticipant(): void
    {
        $token = request()->cookie(self::COOKIE_NAME);

        if (! is_string($token) || $token === '') {
            return;
        }

        $participant = MinigameParticipant::query()
            ->where('minigame_session_id', $this->sessionId)
            ->byCookieToken($token)
            ->with('guesses')
            ->first();

        if ($participant === null) {
            return;
        }

        $this->participantId = $participant->id;
        $this->participantName = $participant->name;
        $this->selectedAnswers = $participant->guesses
            ->pluck('minigame_answer_id', 'minigame_question_id')
            ->map(fn (mixed $answerId): int => (int) $answerId)
            ->all();
        $this->currentStep = $participant->isCompleted() ? 3 : 2;
    }

    private function cookieToken(): string
    {
        $token = request()->cookie(self::COOKIE_NAME);

        if (! is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Cookie::queue(self::COOKIE_NAME, $token, self::COOKIE_DURATION);
        }

        return $token;
    }
}
