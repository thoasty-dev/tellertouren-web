@extends('layouts.page')

@push('page.content')
    @isset($search)
        <div class="mb-6 px-2">
            <h1 class="text-2xl font-bold">Suche</h1>
            <p class="text-base-content/70">{{ $articles->count() }} Treffer für „{{ $search }}“</p>
        </div>
    @endisset

    <div class="grid gap-6">
        @forelse($articles as $article)
            @if($article->attribute('is_short', false))
                <article class="rounded-box bg-base-200 p-5 sm:p-7">
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <span class="badge badge-secondary gap-2 py-4 font-semibold">
                            <img src="{{ asset('img/icons/schneller-teller.svg') }}" class="h-7 w-7" alt="">
                            Schneller Teller
                        </span>
                        <h2 class="text-xl font-bold">{{ $article->title }}</h2>
                    </div>
                    <p class="mb-5 leading-relaxed">{{ $article->markdown }}</p>
                    <x-image-gallery :images="$shortArticleImages[$article->id]" />
                </article>
            @else
                <a class="card overflow-hidden bg-base-200 transition hover:-translate-y-0.5 hover:bg-base-300 hover:shadow-xl sm:card-side" href="{{ route('articles.view.get', ['slug' => $article->attribute('slug'), 'hashid' => $article->attribute('legacy_hashid')]) }}">
                    <figure class="aspect-[16/10] w-full shrink-0 bg-base-300 sm:w-60">
                        <img
                            src="{{ $article->imageUrl }}"
                            @if($article->imageSrcset) srcset="{{ $article->imageSrcset }}" @endif
                            @if($article->imageSizes) sizes="{{ $article->imageSizes }}" @endif
                            class="h-full w-full object-cover"
                            width="1792"
                            height="1024"
                            alt="{{ $article->title }}"
                            @if(! $loop->first) loading="lazy" @endif
                        >
                    </figure>
                    <div class="card-body gap-3 p-5 sm:p-7">
                        <h2 class="card-title">{{ $article->title }}</h2>
                        <p>{{ $article->attribute('description') }}</p>
                        <div class="card-actions mt-auto justify-end">
                            <span class="btn btn-primary btn-sm">Mehr lesen</span>
                        </div>
                    </div>
                </a>
            @endif
        @empty
            <div class="rounded-box bg-base-200 p-8 text-center">
                <h1 class="text-xl font-bold">Keine Artikel gefunden</h1>
                <p class="mt-2 text-base-content/70">Versuche es mit einem anderen Suchbegriff.</p>
            </div>
        @endforelse
    </div>

    @if($articles instanceof \Illuminate\Pagination\LengthAwarePaginator && $articles->hasPages())
        <div class="mt-8">{{ $articles->onEachSide(1)->links() }}</div>
    @endif
@endpush
