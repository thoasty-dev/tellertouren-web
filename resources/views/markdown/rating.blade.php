<section class="not-prose my-8 rounded-box bg-base-200 p-5" aria-label="Bewertung">
    <div class="mb-5 flex items-center justify-between gap-4">
        <h3 class="text-lg font-bold">Unsere Bewertung</h3>
        <span class="text-2xl font-black text-primary">{{ number_format($overall, 1, ',', '.') }} / 10</span>
    </div>
    <dl class="grid gap-3">
        @foreach($scores as $label => $score)
            <div class="grid grid-cols-[8rem_1fr_2.5rem] items-center gap-3 text-sm">
                <dt>{{ $label }}</dt>
                <dd><progress class="progress progress-primary w-full" value="{{ $score }}" max="10"></progress></dd>
                <dd class="text-right font-semibold">{{ number_format($score, 1, ',', '.') }}</dd>
            </div>
        @endforeach
    </dl>
</section>
