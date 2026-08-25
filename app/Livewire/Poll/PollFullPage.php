<?php

namespace App\Livewire\Poll;

use App\Models\Category;
use App\Models\PollQuestion;
use App\Models\PollVote;
use App\Poll\CastPollVote;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PollFullPage extends Component
{
    private const COOKIE_NAME = 'poll_token';

    private const COOKIE_DURATION = 60 * 24 * 365;

    #[Locked]
    public int $categoryId;

    #[Locked]
    public string $categoryName;

    /** @var array<int, int> */
    public array $selectedAnswers = [];

    /** @var array<int, int> */
    public array $votedQuestions = [];

    public function mount(int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->categoryName = Category::query()->findOrFail($categoryId)->name;
        $this->loadVotes();
    }

    public function render(): View
    {
        $questions = PollQuestion::query()
            ->active()
            ->forCategory($this->categoryId)
            ->withCount('votes')
            ->with(['answers' => fn (HasMany $answers): HasMany => $answers->withCount('votes')])
            ->get();

        return view('livewire.poll.poll-full-page', [
            'questions' => $questions,
            'answeredCount' => count($this->votedQuestions),
            'totalQuestions' => $questions->count(),
        ]);
    }

    public function vote(int $questionId, CastPollVote $castVote): void
    {
        $this->validate([
            "selectedAnswers.{$questionId}" => ['required', 'integer'],
        ]);

        $token = $this->cookieToken();
        $castVote->handle(
            categoryId: $this->categoryId,
            questionId: $questionId,
            answerId: (int) $this->selectedAnswers[$questionId],
            ipAddress: request()->ip(),
            cookieToken: $token,
        );

        $actualAnswer = PollVote::query()
            ->forQuestion($questionId)
            ->byIpOrCookie(request()->ip(), $token)
            ->value('poll_answer_id');

        if ($actualAnswer !== null) {
            $this->selectedAnswers[$questionId] = (int) $actualAnswer;
            $this->votedQuestions[$questionId] = (int) $actualAnswer;
        }
    }

    private function loadVotes(): void
    {
        $votes = PollVote::query()
            ->byIpOrCookie(request()->ip(), request()->cookie(self::COOKIE_NAME))
            ->whereHas('question', fn (Builder $query): Builder => $query->forCategory($this->categoryId))
            ->get();

        foreach ($votes as $vote) {
            $this->votedQuestions[$vote->poll_question_id] = $vote->poll_answer_id;
            $this->selectedAnswers[$vote->poll_question_id] = $vote->poll_answer_id;
        }
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
