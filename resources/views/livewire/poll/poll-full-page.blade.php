<div class="grid gap-6">
    <div class="rounded-box bg-primary/10 p-5">
        <h1 class="text-2xl font-bold">Tellertouren-Umfrage: {{ $categoryName }}</h1>
        <p class="mt-2">{{ $answeredCount }} von {{ $totalQuestions }} Fragen beantwortet</p>
        <progress class="progress progress-primary mt-3 w-full" value="{{ $answeredCount }}" max="{{ max(1, $totalQuestions) }}"></progress>
    </div>
    @foreach($questions as $question)
        <section class="rounded-box bg-base-200 p-5" wire:key="poll-question-{{ $question->id }}">
            <div class="mb-4 flex items-start justify-between gap-3">
                <h2 class="text-lg font-bold">{{ $question->question_text }}</h2>
                @isset($votedQuestions[$question->id])<span class="badge badge-success">Fertig</span>@endisset
            </div>
            <div class="grid gap-3">
                @foreach($question->answers as $answer)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg bg-base-100 p-3">
                        <input type="radio" wire:model="selectedAnswers.{{ $question->id }}" value="{{ $answer->id }}" class="radio radio-primary" @disabled(isset($votedQuestions[$question->id]))>
                        <span class="grow">{{ $answer->answer_text }}</span>
                        @isset($votedQuestions[$question->id])<span class="text-sm font-semibold">{{ number_format($answer->votePercentage($question->totalVotes()), 1, ',', '.') }} %</span>@endisset
                    </label>
                @endforeach
            </div>
            @unless(isset($votedQuestions[$question->id]))
                <button type="button" wire:click="vote({{ $question->id }})" class="btn btn-primary btn-sm mt-4">Abstimmen</button>
            @endunless
        </section>
    @endforeach
</div>
