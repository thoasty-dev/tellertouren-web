<?php

namespace Tests\Feature\Blog;

use App\Blog\ArticleCatalog;
use App\Blog\ArticleImageManifest;
use App\Blog\ArticlePresentation;
use App\Blog\LegacyArticleIdCodec;
use Develate\Blogframe\BlogframeManager;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class StaticArticleContentTest extends TestCase
{
    public function test_all_exported_articles_load_and_drafts_are_hidden(): void
    {
        $allArticles = app(BlogframeManager::class)->all('de')->keyBy('id');
        $publishedArticles = app(ArticleCatalog::class)->published();

        $this->assertCount(10, $allArticles);
        $this->assertCount(8, $publishedArticles);
        $this->assertSame(range(1, 10), $allArticles->keys()->sort()->values()->all());
        $this->assertSame([8, 7, 6, 5, 4, 3, 2, 1], $publishedArticles->pluck('id')->all());
        $this->assertNull($allArticles[9]->attribute('published_at'));
        $this->assertNull($allArticles[10]->attribute('published_at'));

        $expectedHashes = [
            1 => '5d1wl4', 2 => 'gp24ln', 3 => 'ypjrl4', 4 => 'ml8rp3', 5 => 'epz9d4',
            6 => 'np4ndr', 7 => 'ep9opr', 8 => 'mpnwp2', 9 => 'xpg6lm', 10 => 'jpogdm',
        ];
        $expectedLocations = [
            1 => ['Venedig', 'Italien', '3,1'],
            2 => ['Trasaghis', 'Italien', '8,2'],
            3 => ['Udine', 'Italien', null],
            4 => ['Spittal an der Drau', 'Österreich', '7,0'],
            5 => ['Villach', 'Österreich', '6,7'],
            6 => ['Grades (Metnitz)', 'Österreich', '8,0'],
            7 => ['Velden am Wörthersee', 'Österreich', '7,4'],
            8 => ['Techendorf', 'Österreich', null],
            9 => [null, null, null],
            10 => [null, null, null],
        ];

        foreach ($allArticles as $article) {
            $this->assertSame($expectedHashes[$article->id], $article->attribute('legacy_hashid'));
            $this->assertSame($expectedLocations[$article->id], [
                $article->attribute('city'),
                $article->attribute('country'),
                $article->attribute('rating'),
            ]);
            $this->assertNotSame('', trim($article->title));
            $this->assertNotSame('', trim($article->markdown));
            $this->assertStringNotContainsString('{{galleryMasonry', $article->html);
            $this->assertStringNotContainsString('{{restaurantRating', $article->html);
            $this->assertStringNotContainsString('{{hotelRating', $article->html);
            $this->assertFileExists(resource_path('blog/images/'.$article->attribute('image')));
        }
    }

    public function test_every_manifest_picture_has_verified_static_files_and_metadata(): void
    {
        $manifest = app(ArticleImageManifest::class)->all();

        $this->assertCount(74, $manifest);

        foreach ($manifest as $id => $image) {
            $this->assertSame((int) $id, $image['id']);
            $this->assertContains($image['article_id'], range(1, 10));
            $this->assertArrayHasKey('alt', $image);

            $servedPath = resource_path('blog/images/'.$image['path']);
            $originalPath = resource_path('blog/images/'.$image['original_path']);
            $this->assertFileExists($servedPath);
            $this->assertFileExists($originalPath);
            $this->assertSame($image['sha256'], hash_file('sha256', $originalPath));
            $this->assertSame($image['mime_type'], mime_content_type($servedPath));

            $dimensions = getimagesize($servedPath);
            $this->assertIsArray($dimensions);
            $this->assertSame($image['width'], $dimensions[0]);
            $this->assertSame($image['height'], $dimensions[1]);
        }

        $preservedHeicImages = collect($manifest)
            ->filter(fn (array $image): bool => $image['path'] !== $image['original_path']);

        $this->assertCount(5, $preservedHeicImages);
        $preservedHeicImages->each(function (array $image): void {
            $this->assertSame('webp', pathinfo($image['path'], PATHINFO_EXTENSION));
            $this->assertSame('heic', pathinfo($image['original_path'], PATHINFO_EXTENSION));
        });
    }

    public function test_no_extra_picture_assets_were_exported(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('blog/images/pictures'), FilesystemIterator::SKIP_DOTS),
        );
        $files = collect(iterator_to_array($iterator))
            ->filter(fn (mixed $file): bool => $file->isFile());

        $this->assertCount(79, $files);
    }

    public function test_catalog_lookup_filtering_search_pagination_and_toc_use_static_content(): void
    {
        $catalog = app(ArticleCatalog::class);
        $codec = app(LegacyArticleIdCodec::class);

        $this->assertSame([7, 5, 4, 1], $catalog->forCategory('restaurants')->pluck('id')->all());
        $this->assertSame([6, 2], $catalog->forCategory('hotels')->pluck('id')->all());
        $this->assertSame([7], $catalog->search('aqua')->pluck('id')->all());
        $this->assertTrue($catalog->search('habichtshöhe')->isEmpty());
        $this->assertSame(8, $catalog->paginate()->total());
        $this->assertSame(15, $catalog->paginate()->perPage());

        foreach ([1 => '5d1wl4', 2 => 'gp24ln', 3 => 'ypjrl4', 4 => 'ml8rp3'] as $id => $hash) {
            $this->assertSame($hash, $codec->encode($id));
            $this->assertSame($id, $codec->decode($hash));
            $this->assertSame($id, $catalog->findPublishedByHash($hash)?->id);
        }

        $this->assertNull($catalog->findPublishedByHash('xpg6lm'));
        $this->assertNull($catalog->findPublishedByHash('abcdef'));

        $article = $catalog->findPublished(1);
        $this->assertNotNull($article);
        $tableOfContents = app(ArticlePresentation::class)->present($article)->tableOfContents;
        $this->assertNotEmpty($tableOfContents);
        $this->assertSame('erster-eindruck--charme-trügt', $tableOfContents[0]['id']);
    }
}
