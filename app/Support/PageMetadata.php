<?php

namespace App\Support;

class PageMetadata
{
    public string $title = 'Tellertouren';

    public ?string $description = null;

    public ?string $canonical = null;

    public ?string $image = null;

    /** @var array<string, mixed>|null */
    public ?array $jsonLd = null;

    public function set(
        string $title,
        ?string $description = null,
        ?string $canonical = null,
        ?string $image = null,
        ?array $jsonLd = null,
    ): self {
        $this->title = $title === 'Tellertouren' ? $title : $title.' | Tellertouren';
        $this->description = $description;
        $this->canonical = $canonical;
        $this->image = $image;
        $this->jsonLd = $jsonLd;

        return $this;
    }

    /** @return array<string, mixed> */
    public function openGraph(): array
    {
        return array_filter([
            'og:title' => $this->title,
            'og:description' => $this->description,
            'og:url' => $this->canonical,
            'og:image' => $this->image,
            'og:type' => $this->jsonLd === null ? 'website' : 'article',
            'og:locale' => 'de_DE',
            'og:site_name' => 'Tellertouren',
        ]);
    }
}
