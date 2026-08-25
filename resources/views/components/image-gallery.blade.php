@props(['images'])

<div {{ $attributes->merge(['class' => 'photoswipe grid grid-cols-2 gap-2 overflow-hidden rounded-box sm:grid-cols-3']) }}>
    @foreach($images as $image)
        <a href="{{ $image['original_url'] }}" target="_blank" data-pswp-width="{{ $image['width'] }}" data-pswp-height="{{ $image['height'] }}" class="group aspect-square overflow-hidden bg-base-300">
            <img src="{{ $image['url'] }}" @if($image['srcset']) srcset="{{ $image['srcset'] }}" @endif sizes="{{ $image['sizes'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" width="{{ $image['width'] }}" height="{{ $image['height'] }}" alt="{{ $image['alt'] ?? '' }}" loading="lazy">
        </a>
    @endforeach
</div>
