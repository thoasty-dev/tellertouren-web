@props(['label', 'value'])

<div>
    <label class="mb-2 block text-sm font-semibold">{{ $label }}</label>
    <div class="join flex">
        <input class="input input-bordered join-item min-w-0 grow" type="text" value="{{ $value }}" readonly>
        <button type="button" class="btn btn-primary join-item js-copy-link" data-copy-value="{{ $value }}" aria-label="Link kopieren"><i class="fas fa-copy"></i></button>
    </div>
</div>
