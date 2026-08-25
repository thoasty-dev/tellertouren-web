<?php

namespace App\Markdown;

use App\Blog\ArticleImageManifest;
use Develate\CommonmarkCustomtags\Customtag;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use Stringable;

final class GalleryMasonryTag extends Customtag
{
    public function __construct(
        private readonly ArticleImageManifest $images,
        private readonly Factory $views,
    ) {}

    public function identifier(): string
    {
        return 'galleryMasonry';
    }

    /**
     * @param  array<int|string, string>  $arguments
     * @return array{ids: list<int>, variant: int}
     */
    public function makeArguments(array $arguments): array
    {
        $rawIds = $arguments['ids'] ?? $arguments[0] ?? '';
        $ids = collect(explode(',', (string) $rawIds))
            ->filter(fn (string $id): bool => ctype_digit($id))
            ->map(fn (string $id): int => (int) $id)
            ->values()
            ->all();

        if ($ids === []) {
            throw new InvalidArgumentException('galleryMasonry requires at least one picture ID.');
        }

        return [
            'ids' => $ids,
            'variant' => max(0, min(3, (int) ($arguments['v'] ?? 0))),
        ];
    }

    /** @param array{ids: list<int>, variant: int} $arguments */
    public function render(mixed $arguments, mixed $globals): Stringable|string|null
    {
        $html = $this->views->make('markdown.gallery-masonry', [
            'images' => $this->images->responsive($arguments['ids']),
            'variant' => $arguments['variant'],
        ])->render();

        return new HtmlString($html);
    }
}
