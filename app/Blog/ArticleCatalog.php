<?php

namespace App\Blog;

use Develate\Blogframe\Article;
use Develate\Blogframe\BlogframeManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ArticleCatalog
{
    public function __construct(
        private readonly BlogframeManager $blogframe,
        private readonly LegacyArticleIdCodec $codec,
    ) {}

    /**
     * @return Collection<int, Article>
     */
    public function published(): Collection
    {
        return $this->blogframe
            ->all('de')
            ->filter(fn (Article $article): bool => filled($article->attribute('published_at')))
            ->sortByDesc(fn (Article $article): string => (string) $article->attribute('published_at'))
            ->values();
    }

    public function paginate(int $perPage = 15, ?int $page = null): LengthAwarePaginator
    {
        $articles = $this->published();
        $page ??= LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            items: $articles->forPage($page, $perPage)->values(),
            total: $articles->count(),
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ],
        );
    }

    /**
     * @return Collection<int, Article>
     */
    public function search(string $query, int $limit = 10): Collection
    {
        $needle = Str::of($query)->trim()->lower()->toString();

        if ($needle === '') {
            return collect();
        }

        return $this->published()
            ->filter(function (Article $article) use ($needle): bool {
                return Str::of($article->title.' '.$article->markdown)->lower()->contains($needle);
            })
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, Article>
     */
    public function forTag(string $slug): Collection
    {
        return $this->published()
            ->filter(function (Article $article) use ($slug): bool {
                $tags = $article->attribute('tags', []);

                return is_array($tags) && collect($tags)->contains(
                    fn (mixed $tag): bool => Str::slug((string) $tag) === $slug,
                );
            })
            ->values();
    }

    /**
     * @return Collection<int, Article>
     */
    public function forCategory(string $category): Collection
    {
        return $this->published()
            ->filter(fn (Article $article): bool => $article->attribute('category') === $category)
            ->values();
    }

    public function findPublished(int $id): ?Article
    {
        return $this->published()->firstWhere('id', $id);
    }

    public function findPublishedByHash(string $hash): ?Article
    {
        $id = $this->codec->decode($hash);

        if ($id === null) {
            return null;
        }

        $article = $this->findPublished($id);

        if ($article === null || ! hash_equals($this->hash($article), $hash)) {
            return null;
        }

        return $article;
    }

    public function hash(Article $article): string
    {
        $storedHash = $article->attribute('legacy_hashid');

        return is_string($storedHash) && $storedHash !== ''
            ? $storedHash
            : $this->codec->encode($article->id);
    }

    public function canonicalUrl(Article $article): string
    {
        return route('articles.view.get', [
            'slug' => $article->attribute('slug'),
            'hashid' => $this->hash($article),
        ]);
    }

    public function imageUrl(string $path): string
    {
        return $this->blogframe->imageUrl($path);
    }
}
