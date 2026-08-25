@extends('layouts.page')

@push('page.content')
    <article class="mx-auto max-w-4xl pb-8">
        <header class="mb-8 flex flex-col gap-5">
            <div class="px-2 md:order-2">
                <h1 class="text-3xl font-bold leading-tight sm:text-4xl">{{ $article->title }}</h1>
                <p class="mt-3 text-base-content/70">{{ $article->attribute('description') }}</p>
                <p class="mt-3 text-xs italic text-base-content/50">Veröffentlicht am {{ \Illuminate\Support\Carbon::parse($article->attribute('published_at'))->format('d.m.Y') }}</p>
            </div>
            @unless($article->attribute('is_short', false))
                <figure class="overflow-hidden rounded-box bg-base-300 md:order-1">
                    <img src="{{ $article->imageUrl }}" @if($article->imageSrcset) srcset="{{ $article->imageSrcset }}" @endif sizes="(min-width: 1024px) 896px, 100vw" class="aspect-[16/9] h-full w-full object-cover" width="1792" height="1024" alt="{{ $article->title }}">
                </figure>
            @endunless
        </header>

        <div class="article-content prose prose-sm max-w-none px-2 sm:prose-base dark:prose-invert">
            {!! $presented->html !!}
        </div>

        @if($article->attribute('is_short', false))
            <div class="mt-7"><x-image-gallery :images="$shortArticleImages" /></div>
        @elseif($presented->tableOfContents !== [])
            <aside class="mt-10 rounded-box bg-base-200 p-5">
                <h2 class="mb-3 text-lg font-bold">Inhalt</h2>
                <ol class="grid gap-2 text-sm">
                    @foreach($presented->tableOfContents as $heading)
                        <li @class(['pl-4' => $heading['level'] === 2])><a class="link link-hover" href="#{{ $heading['id'] }}">{{ $heading['label'] }}</a></li>
                    @endforeach
                </ol>
            </aside>
        @endif

        @if(in_array($article->attribute('category_id'), [1, 2], true))
            <div class="mt-8"><livewire:poll.poll-widget :category-id="$article->attribute('category_id')" /></div>
        @endif
    </article>
@endpush
