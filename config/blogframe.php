<?php

use Intervention\Image\Drivers\Gd\Driver;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;

return [
    'paths' => [
        'articles' => resource_path('blog/articles'),
        'images' => resource_path('blog/images'),
    ],

    'languages' => [
        'default' => 'de',
        'fallback' => 'de',
    ],

    'commonmark' => [
        'options' => [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'min_heading_level' => 1,
                'max_heading_level' => 2,
                'insert' => 'none',
                'id_prefix' => '',
                'apply_id_to_heading' => true,
                'fragment_prefix' => '',
            ],
            'external_link' => [
                'internal_hosts' => [parse_url((string) config('app.url'), PHP_URL_HOST)],
                'open_in_new_window' => true,
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ],

        'extensions' => [
            AttributesExtension::class,
            DisallowedRawHtmlExtension::class,
            ExternalLinkExtension::class,
            HeadingPermalinkExtension::class,
            SmartPunctExtension::class,
        ],
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
