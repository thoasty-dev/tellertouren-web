@extends('layouts.page')

@push('page.content')
    <livewire:minigame.minigame-participant-session :secret="$secret" />
@endpush
