<div class="my-8 rounded-box border border-base-300 bg-base-200 p-5">
    @if($question)
        <div class="mb-4 flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-primary">Kurze Umfrage</p>
                <h2 class="mt-1 text-lg font-bold">{{ $question->question_text }}</h2>
            </div>
            @if($hasVoted)<span class="badge badge-success">Abgestimmt</span>@endif
        </div>
        <form wire:submit="vote" class="grid gap-3">
            @foreach($question->answers as $answer)
                <label class="flex cursor-pointer items-center gap-3 rounded-lg bg-base-100 p-3 transition hover:bg-base-300">
                    <input type="radio" wire:model="selectedAnswerId" value="{{ $answer->id }}" class="radio radio-primary" @disabled($hasVoted)>
                    <span class="grow">{{ $answer->answer_text }}</span>
                    @if($hasVoted)<span class="text-sm font-semibold">{{ number_format($answer->votePercentage($question->totalVotes()), 1, ',', '.') }} %</span>@endif
                </label>
            @endforeach
            @error('selectedAnswerId')<p class="text-sm text-error">Bitte wähle eine Antwort aus.</p>@enderror
            <div class="mt-2 flex flex-wrap justify-between gap-3">
                <a class="btn btn-ghost btn-sm" href="{{ $fullPollRoute }}">Alle Fragen</a>
                @unless($hasVoted)<button class="btn btn-primary btn-sm" type="submit">Abstimmen</button>@endunless
            </div>
        </form>
    @endif
</div>
