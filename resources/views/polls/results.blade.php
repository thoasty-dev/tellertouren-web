@extends('layouts.page')

@push('page.content')
    <div class="mx-auto max-w-3xl">
        <h1 class="mb-6 text-3xl font-bold">Umfrageergebnisse</h1>
        <div class="grid gap-8">
            @foreach($categories as $category)
                <section><h2 class="mb-4 text-2xl font-bold">{{ $category->name }}</h2>
                    <div class="grid gap-5">
                        @foreach($category->pollQuestions as $question)
                            <div class="rounded-box bg-base-200 p-5">
                                <h3 class="font-bold">{{ $question->question_text }}</h3>
                                <div class="mt-4 grid gap-3">
                                    @foreach($question->answers as $answer)
                                        <div><div class="flex justify-between gap-3 text-sm"><span>{{ $answer->answer_text }}</span><span>{{ number_format($answer->votePercentage($question->totalVotes()), 1, ',', '.') }} %</span></div><progress class="progress progress-primary w-full" value="{{ $answer->voteCount() }}" max="{{ max(1, $question->totalVotes()) }}"></progress></div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endpush
