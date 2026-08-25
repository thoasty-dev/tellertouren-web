<?php

namespace App\Markdown;

use Develate\CommonmarkCustomtags\Customtag;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\HtmlString;
use Stringable;

final class RestaurantRatingTag extends Customtag
{
    public function __construct(private readonly Factory $views) {}

    public function identifier(): string
    {
        return 'restaurantRating';
    }

    /** @param array<int|string, string> $arguments */
    public function render(mixed $arguments, mixed $globals): Stringable|string|null
    {
        return new HtmlString($this->views->make('markdown.rating', [
            'overall' => $this->score($arguments['overall'] ?? '0'),
            'scores' => [
                'Lokalität' => $this->score($arguments['locality'] ?? '0'),
                'Service' => $this->score($arguments['service'] ?? '0'),
                'Aussehen' => $this->score($arguments['looks'] ?? '0'),
                'Geschmack' => $this->score($arguments['taste'] ?? '0'),
                'Preis-Leistung' => $this->score($arguments['price'] ?? '0'),
            ],
        ])->render());
    }

    private function score(string $value): float
    {
        return (float) str_replace(',', '.', $value);
    }
}
