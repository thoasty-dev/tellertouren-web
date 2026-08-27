@extends('layouts.page')

@php($metadata = app(\App\Support\PageMetadata::class)->set($headline))

@push('layout.head')
    <meta name="robots" content="noindex">
@endpush

@push('page.content')
    <div class="mx-auto max-w-2xl py-10 text-center sm:py-16">
        <p class="font-title text-7xl font-black text-primary sm:text-9xl">{{ $code }}</p>
        <h1 class="mt-4 text-2xl font-bold sm:text-3xl">{{ $headline }}</h1>
        <p class="mx-auto mt-3 max-w-md text-base-content/70">{{ $message }}</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a class="btn btn-primary" href="{{ route('articles.index.get') }}"><i class="fas fa-home"></i> Zur Startseite</a>
            <a class="btn btn-ghost" href="{{ route('minigame.index') }}"><i class="fas fa-gamepad"></i> Zum Minigame</a>
        </div>
    </div>
@endpush
