<x-image-gallery :images="$images" @class([
    'my-8',
    'sm:grid-cols-2' => $variant === 0,
    'sm:grid-cols-3' => $variant === 2,
    'sm:grid-cols-4' => $variant === 3,
]) />
