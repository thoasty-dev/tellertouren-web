<?php

namespace Tests\Feature\Blog;

use App\Blog\ArticleCatalog;
use Database\Seeders\ContentDefinitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicArticleRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ContentDefinitionSeeder::class);
    }

    public function test_all_published_legacy_urls_and_aliases_remain_available(): void
    {
        $articles = app(ArticleCatalog::class)->published();

        foreach ($articles as $article) {
            $parameters = [
                'slug' => $article->attribute('slug'),
                'hashid' => $article->attribute('legacy_hashid'),
            ];

            $this->get(route('articles.view.get', $parameters))
                ->assertOk()
                ->assertSee($article->title)
                ->assertSee('<link rel="canonical" href="'.route('articles.view.get', $parameters).'">', false);

            foreach (['articles.restaurant.view.get', 'articles.hotel.view.get'] as $routeName) {
                $this->get(route($routeName, $parameters))
                    ->assertOk()
                    ->assertSee('<link rel="canonical" href="'.route('articles.view.get', $parameters).'">', false);
            }
        }
    }

    public function test_article_hashes_enforce_canonical_slugs_and_draft_privacy(): void
    {
        $this->get('/article/falscher-slug_5d1wl4')
            ->assertRedirectToRoute('articles.view.get', [
                'slug' => 'osteria-al-ponte-la-patatina-in-venedig-italien',
                'hashid' => '5d1wl4',
            ], 301);

        $this->get('/article/ungueltig_abcdef')->assertNotFound();

        foreach (['xpg6lm', 'jpogdm'] as $draftHash) {
            foreach (['article', 'restaurant', 'hotel'] as $prefix) {
                $this->get("/{$prefix}/entwurf_{$draftHash}")->assertNotFound();
            }
        }
    }

    public function test_listings_search_feed_and_legal_pages_exclude_drafts(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['OASEE am Weißensee', 'Restaurant AQUA', 'Almhütte in Grades'])
            ->assertDontSee('Ferienpark Roompot Gulpen')
            ->assertDontSee('Restaurant Habichtshöhe');

        $this->get('/search?q=AQUA')->assertOk()->assertSee('Restaurant AQUA');
        $this->get('/search?q=Habichtshöhe')->assertOk()->assertDontSee('Restaurant Habichtshöhe');

        $feed = $this->get('/feed')->assertOk()->assertHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
        $this->assertSame(8, substr_count($feed->getContent(), '<entry>'));
        $feed->assertDontSee('Ferienpark Roompot Gulpen')->assertDontSee('Restaurant Habichtshöhe');

        $this->get('/data-privacy')->assertOk();
        $this->get('/site-notice')->assertOk();
    }

    public function test_sitemap_contains_only_published_articles(): void
    {
        Storage::fake('public');

        $this->artisan('sitemap:generate')->assertSuccessful();

        Storage::disk('public')->assertExists('sitemap.xml');
        $sitemap = Storage::disk('public')->get('sitemap.xml');
        $this->assertSame(8, substr_count($sitemap, '/article/'));
        $this->assertStringNotContainsString('roompot-gulpen', $sitemap);
        $this->assertStringNotContainsString('habichtshoehe', $sitemap);
    }

    public function test_blogframe_images_require_an_intact_signature(): void
    {
        $article = app(ArticleCatalog::class)->findPublished(1);
        $this->assertNotNull($article);

        $this->get($article->imageUrl)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/webp');

        $tamperedUrl = preg_replace(
            '/signature=[a-f0-9]+/',
            'signature='.str_repeat('0', 64),
            $article->imageUrl,
        );

        $this->assertIsString($tamperedUrl);
        $this->get($tamperedUrl)->assertForbidden();
    }
}
