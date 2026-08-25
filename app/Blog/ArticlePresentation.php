<?php

namespace App\Blog;

use Develate\Blogframe\Article;
use DOMDocument;
use DOMElement;
use DOMXPath;

class ArticlePresentation
{
    public function present(Article $article): PresentedArticle
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><section id="article-content">'.$article->html.'</section>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $tableOfContents = [];
        $xpath = new DOMXPath($document);

        foreach ($xpath->query('//section[@id="article-content"]//*[self::h1 or self::h2]') ?: [] as $heading) {
            if (! $heading instanceof DOMElement) {
                continue;
            }

            $id = $heading->getAttribute('id');

            if ($id === '') {
                continue;
            }

            $tableOfContents[] = [
                'id' => $id,
                'label' => trim($heading->textContent),
                'level' => (int) substr($heading->tagName, 1),
            ];
        }

        return new PresentedArticle($article->html, $tableOfContents);
    }
}
