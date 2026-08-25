<?php

namespace App\Livewire\Poll;

use App\Models\PollQuestion;
use App\Models\PollVote;
use App\Poll\CastPollVote;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PollWidget extends Component
{
    private const COOKIE_NAME = 'poll_token';

    private const COOKIE_DURATION = 60 * 24 * 365;

    #[Locked]
    public int $categoryId;

    #[Locked]
    public ?int $questionId = null;

    public ?int $selectedAnswerId = null;

    public bool $hasVoted = false;

    public function mount(int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->loadQuestion();
    }

    public function render(): View
    {
        $question = $this->questionId === null
            ? null
            : PollQuestion::query()
                ->active()
                ->forCategory($this->categoryId)
                ->withCount('votes')
                ->with(['answers' => fn (HasMany $answers): HasMany => $answers->withCount('votes')])
                ->find($this->questionId);

        return view('livewire.poll.poll-widget', [
            'question' => $question,
            'fullPollRoute' => route('polls.category.get', ['categoryId' => $this->categoryId]),
        ]);
    }

    public function vote(CastPollVote $castVote): void
    {
        $this->validate(['selectedAnswerId' => ['required', 'integer']]);

        if ($this->questionId === null) {
            return;
        }

        $token = $this->cookieToken();
        $created = $castVote->handle(
            categoryId: $this->categoryId,
            questionId: $this->questionId,
            answerId: (int) $this->selectedAnswerId,
            ipAddress: request()->ip(),
            cookieToken: $token,
        );

        $this->hasVoted = true;

        if (! $created) {
            $this->selectedAnswerId = PollVote::query()
                ->forQuestion($this->questionId)
                ->byIpOrCookie(request()->ip(), $token)
                ->value('poll_answer_id');
        }
    }

    private function loadQuestion(): void
    {
        $votes = PollVote::query()
            ->byIpOrCookie(request()->ip(), request()->cookie(self::COOKIE_NAME))
            ->pluck('poll_question_id');

        $question = PollQuestion::query()
            ->active()
            ->forCategory($this->categoryId)
            ->whereNotIn('id', $votes)
            ->inRandomOrder()
            ->first();

        if ($question === null) {
            $question = PollQuestion::query()
                ->active()
                ->forCategory($this->categoryId)
                ->inRandomOrder()
                ->first();
            $this->hasVoted = $question !== null;
        }

        $this->questionId = $question?->id;

        if ($question !== null && $this->hasVoted) {
            $this->selectedAnswerId = PollVote::query()
                ->forQuestion($question->id)
                ->byIpOrCookie(request()->ip(), request()->cookie(self::COOKIE_NAME))
                ->value('poll_answer_id');
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
