<?php

namespace App\Http\Controllers;

use App\Blog\ArticleCatalog;
use App\Blog\ArticleImageManifest;
use App\Blog\ArticlePresentation;
use App\Http\Requests\SearchArticleRequest;
use App\Support\PageMetadata;
use Develate\Blogframe\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleCatalog $articles,
        private readonly ArticlePresentation $presentation,
        private readonly ArticleImageManifest $images,
        private readonly PageMetadata $metadata,
    ) {}

    public function index(Request $request): View
    {
        $articles = $this->articles->paginate(page: max(1, $request->integer('page', 1)));

        return view('articles.index', [
            'articles' => $articles,
            'shortArticleImages' => $this->shortArticleImages($articles->items()),
            'metadata' => $this->metadata->set(
                title: 'Tellertouren',
                description: 'Entdecke mit Tellertouren die besten Restaurants und Hotels. Wir testen und bewerten für euch und teilen ehrliche Empfehlungen.',
                canonical: route('articles.index.get'),
            ),
        ]);
    }

    public function search(SearchArticleRequest $request): View
    {
        $articles = $this->articles->search($request->queryText());

        return view('articles.index', [
            'articles' => $articles,
            'shortArticleImages' => $this->shortArticleImages($articles->all()),
            'search' => $request->queryText(),
            'metadata' => $this->metadata->set(
                title: 'Suche',
                description: 'Durchsuche die Restaurant- und Hotelberichte von Tellertouren.',
                canonical: route('articles.search.get', array_filter(['q' => $request->queryText()])),
            ),
        ]);
    }

    public function tag(string $slug): View
    {
        $articles = $this->articles->forTag($slug);

        abort_if($articles->isEmpty(), 404);

        return view('articles.index', [
            'articles' => $articles,
            'shortArticleImages' => $this->shortArticleImages($articles->all()),
            'tag' => $slug,
            'metadata' => $this->metadata->set(
                title: 'Tag: '.$slug,
                canonical: route('articles.tag.get', ['slug' => $slug]),
            ),
        ]);
    }

    public function show(string $slug, string $hashid): View|RedirectResponse
    {
        $article = $this->articles->findPublishedByHash($hashid);

        abort_if($article === null, 404);

        if ($slug !== $article->attribute('slug')) {
            return redirect()->to($this->articles->canonicalUrl($article), 301);
        }

        $canonical = $this->articles->canonicalUrl($article);

        return view('articles.show', [
            'article' => $article,
            'presented' => $this->presentation->present($article),
            'shortArticleImages' => $this->articleImages($article),
            'metadata' => $this->metadata->set(
                title: $article->title,
                description: (string) $article->attribute('description'),
                canonical: $canonical,
                image: $article->imageUrl,
                jsonLd: [
                    '@context' => 'https://schema.org',
                    '@type' => 'BlogPosting',
                    'headline' => $article->title,
                    'description' => (string) $article->attribute('description'),
                    'image' => $article->imageUrl,
                    'datePublished' => Carbon::parse((string) $article->attribute('published_at'))->toAtomString(),
                    'dateModified' => Carbon::parse((string) $article->attribute('updated_at'))->toAtomString(),
                    'mainEntityOfPage' => $canonical,
                    'author' => [
                        '@type' => 'Organization',
                        'name' => 'Tellertouren',
                        'url' => config('app.url'),
                    ],
                ],
            ),
        ]);
    }

    public function feed(): Response
    {
        return response()
            ->view('feed.atom', ['articles' => $this->articles->published(), 'catalog' => $this->articles])
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /**
     * @param  array<int, Article>  $articles
     * @return array<int, Collection<int, array<string, mixed>>>
     */
    private function shortArticleImages(array $articles): array
    {
        return collect($articles)
            ->filter(fn (Article $article): bool => (bool) $article->attribute('is_short', false))
            ->mapWithKeys(fn (Article $article): array => [$article->id => $this->articleImages($article)])
            ->all();
    }

    /** @return Collection<int, array<string, mixed>> */
    private function articleImages(Article $article): Collection
    {
        $ids = $article->attribute('pictures', []);

        return $this->images->responsive(is_array($ids) ? array_map('intval', $ids) : []);
    }
}
