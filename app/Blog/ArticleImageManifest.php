<?php

namespace App\Blog;

use Develate\Blogframe\Images\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;

class ArticleImageManifest
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $images = null;

    public function __construct(private readonly ImageService $imageService) {}

    /**
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        return $this->all()[(string) $id]
            ?? throw new RuntimeException("Static picture {$id} is missing from the manifest.");
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, array<string, mixed>>
     */
    public function responsive(array $ids): Collection
    {
        return collect($ids)->map(function (int $id): array {
            $image = $this->find($id);
            $responsive = $this->imageService->responsive((string) Arr::get($image, 'path'), '(min-width: 768px) 50vw, 100vw');

            return [
                ...$image,
                'url' => $responsive->src,
                'original_url' => $responsive->originalUrl,
                'srcset' => $responsive->srcset,
                'sizes' => $responsive->sizes,
            ];
        });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->images !== null) {
            return $this->images;
        }

        $path = resource_path('blog/images/manifest.json');
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('The static article image manifest could not be read.');
        }

        $images = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($images)) {
            throw new RuntimeException('The static article image manifest is invalid.');
        }

        return $this->images = $images;
    }
}
