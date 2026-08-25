<div class="mx-auto max-w-3xl">
    <div class="mb-8 text-center">
        <span class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary/15"><i class="fas fa-gamepad text-3xl text-primary"></i></span>
        <h1 class="text-3xl font-bold">Wie gut kennen dich deine Freunde?</h1>
        <p class="mt-2 text-base-content/70">Beantworte die Fragen und teile danach deinen persönlichen Link.</p>
    </div>

    @if($currentStep === 1)
        <form wire:submit="startGame" class="rounded-box bg-base-200 p-6">
            <label class="form-control">
                <span class="label-text mb-2 font-semibold">Dein Name</span>
                <input wire:model="creatorName" class="input input-bordered w-full" maxlength="50" autocomplete="name" autofocus>
            </label>
            @error('creatorName')<p class="mt-2 text-sm text-error">{{ $message }}</p>@enderror
            <button class="btn btn-primary mt-5 w-full" type="submit"><i class="fas fa-play"></i> Spiel starten</button>
        </form>
    @elseif($currentStep === 2)
        <div class="mb-6">
            <div class="flex justify-between text-sm"><span>Frage {{ $currentQuestionIndex + 1 }} von {{ $totalQuestions }}</span><span>{{ $answeredCount }} beantwortet</span></div>
            <progress class="progress progress-primary mt-2 w-full" value="{{ $answeredCount }}" max="{{ max(1, $totalQuestions) }}"></progress>
        </div>
        @if(isset($questions[$currentQuestionIndex]))
            @php($question = $questions[$currentQuestionIndex])
            <section class="rounded-box bg-base-200 p-6" wire:key="creator-question-{{ $question->id }}">
                <h2 class="text-xl font-bold">{{ $question->question_text }}</h2>
                @if($question->comment)<p class="mt-1 text-sm italic text-base-content/60">{{ $question->comment }}</p>@endif
                <div class="mt-5 grid gap-3">
                    @foreach($question->answers as $answer)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg bg-base-100 p-4 hover:bg-base-300">
                            <input type="radio" wire:model="selectedAnswers.{{ $question->id }}" wire:change="saveAnswer({{ $question->id }})" value="{{ $answer->id }}" class="radio radio-primary">
                            <span>{{ $answer->answer_text }}</span>
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
                <button class="btn btn-primary confetti grow" wire:click="completeQuestions" @disabled($answeredCount < $totalQuestions)>Fertigstellen <i class="fas fa-check"></i></button>
            @endif
        </div>
    @else
        <div class="rounded-box bg-base-200 p-6">
            <div class="text-center"><i class="fas fa-trophy text-5xl text-warning"></i><h2 class="mt-3 text-2xl font-bold">Dein Spiel ist bereit!</h2></div>
            <div class="mt-6 grid gap-4">
                <x-copy-link label="Dein persönlicher Link" :value="$creatorUrl" />
                <x-copy-link label="Link für deine Freunde" :value="$shareUrl" />
            </div>
            <div class="mt-8">
                <div class="mb-4 flex items-center justify-between"><h3 class="text-lg font-bold">Ergebnisse deiner Freunde</h3><span class="badge badge-primary">{{ $participants->count() }}</span></div>
                <div class="grid gap-3" wire:poll.10s>
                    @forelse($participants as $participant)
                        <div class="flex items-center justify-between rounded-lg bg-base-300 p-4">
                            <span class="font-semibold">{{ $participant->name }}</span>
                            <span class="text-xl font-bold">{{ number_format($participant->scorePercentage(), 0) }} %</span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-base-content/60">Noch keine abgeschlossenen Teilnahmen.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
