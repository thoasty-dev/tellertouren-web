<?php

namespace App\Console\Commands;

use App\Blog\ArticleCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the public sitemap from static Blogframe articles';

    public function handle(ArticleCatalog $articles): int
    {
        $xml = view('sitemap', [
            'articles' => $articles->published(),
            'catalog' => $articles,
        ])->render();

        Storage::disk('public')->put('sitemap.xml', $xml);

        $this->components->info('Sitemap generated at storage/app/public/sitemap.xml.');

        return self::SUCCESS;
    }
}
