<?php

namespace App\Blog;

final readonly class PresentedArticle
{
    /**
     * @param  list<array{id: string, label: string, level: int}>  $tableOfContents
     */
    public function __construct(
        public string $html,
        public array $tableOfContents,
    ) {}
}
