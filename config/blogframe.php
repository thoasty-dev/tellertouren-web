<?php

use Intervention\Image\Drivers\Gd\Driver;

return [
    'paths' => [
        'articles' => resource_path('blog/articles'),
        'images' => resource_path('blog/images'),
    ],

    'languages' => [
        // Null delegates to app.locale and app.fallback_locale at query time.
        'default' => null,
        'fallback' => null,
    ],

    'commonmark' => [
        'options' => [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ],

        // Class names are resolved through Laravel's container.
        'extensions' => [],
    ],

    'images' => [
        'serve' => true,
        'base_url' => null,
        'route_prefix' => 'blogframe/images',
        'middleware' => [],
        'allowed_extensions' => ['png', 'jpg', 'jpeg', 'webp', 'gif', 'avif'],
        'widths' => [320, 640, 960, 1280, 1600],
        'fallback_width' => 960,
        'sizes' => '100vw',
        'driver' => Driver::class,
        'quality' => 82,
        'cache_path' => storage_path('framework/cache/blogframe/images'),
        'cache_control' => 'public, max-age=31536000, immutable',
    ],
];
