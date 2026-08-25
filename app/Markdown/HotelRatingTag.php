<?php

namespace App\Markdown;

use Develate\CommonmarkCustomtags\Customtag;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\HtmlString;
use Stringable;

final class HotelRatingTag extends Customtag
{
    public function __construct(private readonly Factory $views) {}

    public function identifier(): string
    {
        return 'hotelRating';
    }

    /** @param array<int|string, string> $arguments */
    public function render(mixed $arguments, mixed $globals): Stringable|string|null
    {
        return new HtmlString($this->views->make('markdown.rating', [
            'overall' => $this->score($arguments['overall'] ?? '0'),
            'scores' => [
                'Lage' => $this->score($arguments['location'] ?? '0'),
                'Service' => $this->score($arguments['service'] ?? '0'),
                'Zustand' => $this->score($arguments['condition'] ?? '0'),
                'Komfort' => $this->score($arguments['comfort'] ?? '0'),
                'Preis-Leistung' => $this->score($arguments['price'] ?? '0'),
            ],
        ])->render());
    }

    private function score(string $value): float
    {
        return (float) str_replace(',', '.', $value);
    }
}
