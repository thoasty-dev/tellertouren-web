@extends('layouts.global')

@push('layout.body')
    <div class="relative mx-auto min-h-screen w-full max-w-5xl overflow-hidden px-4 sm:px-6">
        <header class="relative z-10 flex flex-col items-center gap-5 py-6 sm:py-8">
            <a href="{{ route('articles.index.get') }}" class="transition-opacity hover:opacity-80" aria-label="Tellertouren Startseite">
                <img src="{{ asset('img/logo.svg') }}" class="w-48 max-w-full dark:hidden sm:w-64" width="800" height="804" alt="Tellertouren">
                <img src="{{ asset('img/logo-dark.svg') }}" class="hidden w-48 max-w-full dark:block sm:w-64" width="800" height="804" alt="Tellertouren">
            </a>
            <nav class="flex flex-wrap items-center justify-center gap-2 text-sm" aria-label="Hauptnavigation">
                <a class="btn btn-ghost btn-sm" href="{{ route('articles.index.get') }}">Touren</a>
                <a class="btn btn-ghost btn-sm" href="{{ route('minigame.index') }}">Minigame</a>
                <form action="{{ route('articles.search.get') }}" method="get" class="join">
                    <label class="sr-only" for="site-search">Artikel durchsuchen</label>
                    <input id="site-search" name="q" type="search" value="{{ request('q') }}" class="input input-sm join-item w-40 sm:w-52" placeholder="Suchen …">
                    <button class="btn btn-primary btn-sm join-item" type="submit" aria-label="Suchen"><i class="fas fa-search"></i></button>
                </form>
            </nav>
        </header>

        <main class="relative z-10">
            @stack('page.content')
        </main>
    </div>

    <x-footer />
@endpush
