@extends('layouts.page')

@push('page.content')
    <div class="mx-auto max-w-3xl"><livewire:poll.poll-full-page :category-id="$categoryId" /></div>
@endpush
