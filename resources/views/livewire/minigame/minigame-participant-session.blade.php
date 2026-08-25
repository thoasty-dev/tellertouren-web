<div class="mx-auto max-w-3xl">
    <div class="mb-8 text-center">
        <span class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary/15"><i class="fas fa-users text-3xl text-primary"></i></span>
        <h1 class="text-3xl font-bold">Wie gut kennst du {{ $session->creator_name }}?</h1>
        <p class="mt-2 text-base-content/70">Tippe, welche Antworten {{ $session->creator_name }} gewählt hat.</p>
    </div>

    @if($currentStep === 1)
        <form wire:submit="startPlaying" class="rounded-box bg-base-200 p-6">
            <label class="form-control">
                <span class="label-text mb-2 font-semibold">Dein Name</span>
                <input wire:model="participantName" class="input input-bordered w-full" maxlength="50" autocomplete="name" autofocus>
            </label>
            @error('participantName')<p class="mt-2 text-sm text-error">{{ $message }}</p>@enderror
            <button class="btn btn-primary mt-5 w-full" type="submit"><i class="fas fa-play"></i> Mitspielen</button>
        </form>
    @elseif($currentStep === 2)
        <div class="mb-6">
            <div class="flex justify-between text-sm"><span>Frage {{ $currentQuestionIndex + 1 }} von {{ $totalQuestions }}</span><span>{{ $answeredCount }} beantwortet</span></div>
            <progress class="progress progress-primary mt-2 w-full" value="{{ $answeredCount }}" max="{{ max(1, $totalQuestions) }}"></progress>
        </div>
        @if(isset($questions[$currentQuestionIndex]))
            @php($question = $questions[$currentQuestionIndex])
            <section class="rounded-box bg-base-200 p-6" wire:key="participant-question-{{ $question->id }}">
                <h2 class="text-xl font-bold">{{ $question->guessQuestionFor($session->creator_name) }}</h2>
                @if($question->comment)<p class="mt-1 text-sm italic text-base-content/60">{{ $question->comment }}</p>@endif
                <div class="mt-5 grid gap-3">
                    @foreach($question->answers as $answer)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg bg-base-100 p-4 hover:bg-base-300">
                            <input type="radio" wire:model="selectedAnswers.{{ $question->id }}" wire:change="saveGuess({{ $question->id }})" value="{{ $answer->id }}" class="radio radio-primary">
                            <span>{{ $answer->guessAnswerFor($session->creator_name) }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endif
        @error('questions')<div class="alert alert-error mt-5">{{ $message }}</div>@enderror
        <div class="mt-6 flex gap-3">
            @if($currentQuestionIndex > 0)<button class="btn btn-outline grow" wire:click="previousQuestion"><i class="fas fa-arrow-left"></i> Zurück</button>@endif
            @if($currentQuestionIndex < $totalQuestions - 1)
                <button class="btn btn-primary grow" wire:click="nextQuestion" @disabled(! isset($selectedAnswers[$questions[$currentQuestionIndex]->id]))>Weiter <i class="fas fa-arrow-right"></i></button>
            @else
                <button class="btn btn-primary confetti grow" wire:click="completeQuestions" @disabled($answeredCount < $totalQuestions)>Auswerten <i class="fas fa-check"></i></button>
            @endif
        </div>
    @elseif($participant)
        <div class="rounded-box bg-base-200 p-7 text-center">
            <i class="fas fa-trophy text-6xl text-warning"></i>
            <h2 class="mt-4 text-2xl font-bold">Dein Ergebnis</h2>
            <p class="mt-3 text-6xl font-black text-primary">{{ number_format($participant->scorePercentage(), 0) }} %</p>
            <p class="mt-2">{{ $participant->correctGuessesCount() }} von {{ $participant->totalGuessesCount() }} Antworten richtig</p>
        </div>
        <div class="mt-7 grid gap-4">
            @foreach($questions as $question)
                @php($guess = $participantGuesses[$question->id] ?? null)
                @php($creatorAnswer = $creatorAnswers[$question->id] ?? null)
                <section @class(['rounded-box p-4 ring-2', 'bg-success/10 ring-success/50' => $guess?->is_correct, 'bg-error/10 ring-error/50' => ! $guess?->is_correct])>
                    <h3 class="font-bold">{{ $question->guessQuestionFor($session->creator_name) }}</h3>
                    @if($creatorAnswer)<p class="mt-2 text-sm">Richtig: {{ $creatorAnswer->answer->guessAnswerFor($session->creator_name) }}</p>@endif
                    @if($guess && ! $guess->is_correct)<p class="mt-1 text-sm">Dein Tipp: {{ $guess->answer->guessAnswerFor($session->creator_name) }}</p>@endif
                </section>
            @endforeach
        </div>
    @endif
</div>
