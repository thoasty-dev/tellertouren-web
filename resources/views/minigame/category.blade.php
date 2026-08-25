@extends('layouts.page')

@push('page.content')
    <livewire:minigame.minigame-creator-session :category-id="$categoryId" :secret="$secret" />
@endpush
