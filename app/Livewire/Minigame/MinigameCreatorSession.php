<?php

namespace App\Livewire\Minigame;

use App\Minigame\SaveCreatorAnswer;
use App\Models\MinigameQuestion;
use App\Models\MinigameSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MinigameCreatorSession extends Component
{
    #[Locked]
    public int $categoryId;

    #[Locked]
    public ?int $sessionId = null;

    #[Locked]
    public ?string $secret = null;

    public string $creatorName = '';

    /** @var array<int, int> */
    public array $selectedAnswers = [];

    public int $currentStep = 1;

    public int $currentQuestionIndex = 0;

    public function mount(int $categoryId, ?string $secret = null): void
    {
        abort_unless(MinigameQuestion::query()->active()->forCategory($categoryId)->exists(), 404);

        $this->categoryId = $categoryId;
        $this->secret = $secret;

        if ($secret !== null) {
            $this->loadSession($secret);
        }
    }

    public function render(): View
    {
        $session = $this->sessionId === null
            ? null
            : MinigameSession::query()
                ->where('category_id', $this->categoryId)
                ->withCount(['participants as completed_participants_count' => fn (Builder $query): Builder => $query->whereNotNull('completed_at')])
                ->findOrFail($this->sessionId);
        $questions = $this->currentStep < 2
            ? collect()
            : MinigameQuestion::query()->active()->forCategory($this->categoryId)->ordered()->with('answers')->get();
        $participants = $session?->participants()
            ->whereNotNull('completed_at')
            ->withCount([
                'guesses',
                'guesses as correct_guesses_count' => fn (Builder $query): Builder => $query->where('is_correct', true),
            ])
            ->latest('completed_at')
            ->get() ?? collect();

        return view('livewire.minigame.minigame-creator-session', [
            'session' => $session,
            'questions' => $questions,
            'participants' => $participants,
            'totalQuestions' => $questions->count(),
            'answeredCount' => count($this->selectedAnswers),
            'creatorUrl' => $this->creatorUrl(),
            'shareUrl' => $this->shareUrl(),
        ]);
    }

    public function startGame(): void
    {
        $validated = $this->validate([
            'creatorName' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $session = MinigameSession::query()->create([
            'category_id' => $this->categoryId,
            'secret' => MinigameSession::generateSecret(),
            'creator_name' => $validated['creatorName'],
        ]);

        $this->redirect(route('minigame.session', [
            'categoryId' => $this->categoryId,
            'secret' => $session->secret,
        ]));
    }

    public function saveAnswer(int $questionId, SaveCreatorAnswer $saveAnswer): void
    {
        if ($this->sessionId === null || ! isset($this->selectedAnswers[$questionId])) {
            return;
        }

        $saveAnswer->handle(
            sessionId: $this->sessionId,
            categoryId: $this->categoryId,
            questionId: $questionId,
            answerId: (int) $this->selectedAnswers[$questionId],
        );
    }

    public function nextQuestion(): void
    {
        $lastIndex = max(0, MinigameQuestion::query()->active()->forCategory($this->categoryId)->count() - 1);
        $this->currentQuestionIndex = min($this->currentQuestionIndex + 1, $lastIndex);
    }

    public function previousQuestion(): void
    {
        $this->currentQuestionIndex = max(0, $this->currentQuestionIndex - 1);
    }

    public function completeQuestions(): void
    {
        if ($this->sessionId === null) {
            return;
        }

        DB::transaction(function (): void {
            $session = MinigameSession::query()
                ->where('category_id', $this->categoryId)
                ->lockForUpdate()
                ->findOrFail($this->sessionId);
            $questionIds = MinigameQuestion::query()->active()->forCategory($this->categoryId)->pluck('id');
            $answeredIds = $session->sessionAnswers()->whereIn('minigame_question_id', $questionIds)->pluck('minigame_question_id');

            if ($questionIds->diff($answeredIds)->isNotEmpty()) {
                $this->addError('questions', 'Bitte beantworte zuerst alle Fragen.');

                return;
            }

            $session->update(['completed_at' => now()]);
            $this->currentStep = 3;
            $this->dispatch('minigame-completed');
        });
    }

    private function loadSession(string $secret): void
    {
        $session = MinigameSession::query()
            ->where('category_id', $this->categoryId)
            ->bySecret($secret)
            ->with('sessionAnswers')
            ->firstOrFail();

        $this->sessionId = $session->id;
        $this->creatorName = $session->creator_name;
        $this->selectedAnswers = $session->sessionAnswers
            ->pluck('minigame_answer_id', 'minigame_question_id')
            ->map(fn (mixed $answerId): int => (int) $answerId)
            ->all();
        $this->currentStep = $session->isCompleted() ? 3 : 2;

        if (! $session->isCompleted()) {
            $questionIds = MinigameQuestion::query()->active()->forCategory($this->categoryId)->ordered()->pluck('id')->values();
            $firstUnanswered = $questionIds->search(fn (int $id): bool => ! isset($this->selectedAnswers[$id]));
            $this->currentQuestionIndex = $firstUnanswered === false ? max(0, $questionIds->count() - 1) : (int) $firstUnanswered;
        }
    }

    private function creatorUrl(): string
    {
        return $this->secret === null
            ? route('minigame.category', ['categoryId' => $this->categoryId])
            : route('minigame.session', ['categoryId' => $this->categoryId, 'secret' => $this->secret]);
    }

    private function shareUrl(): ?string
    {
        return $this->secret === null ? null : route('minigame.play', ['secret' => $this->secret]);
    }
}
