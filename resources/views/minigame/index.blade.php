@extends('layouts.page')

@push('page.content')
    <div class="mx-auto max-w-3xl text-center">
        <h1 class="text-3xl font-bold">Wie gut kennen dich deine Freunde?</h1>
        <p class="mx-auto mt-3 max-w-xl text-base-content/70">Wähle eine Kategorie, beantworte die Fragen und schicke deinen Freunden den Link.</p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            @foreach($categories as $category)
                <a href="{{ route('minigame.category', ['categoryId' => $category->id]) }}" class="card bg-base-200 p-7 transition hover:-translate-y-1 hover:shadow-xl"><i class="fas fa-{{ $category->id === 1 ? 'utensils' : 'hotel' }} mb-4 text-4xl text-primary"></i><h2 class="text-xl font-bold">{{ $category->name }}</h2><span class="btn btn-primary mt-5">Auswählen</span></a>
            @endforeach
        </div>
    </div>
@endpush
