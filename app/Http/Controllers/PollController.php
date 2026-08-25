<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\PageMetadata;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollController extends Controller
{
    public function __construct(private readonly PageMetadata $metadata) {}

    public function category(int $categoryId): View
    {
        $category = Category::query()
            ->whereHas('pollQuestions', fn (Builder $query): Builder => $query->active())
            ->findOrFail($categoryId);

        return view('polls.full', [
            'categoryId' => $categoryId,
            'category' => $category,
            'metadata' => $this->metadata->set(
                title: 'Umfrage: '.$category->name,
                canonical: route('polls.category.get', ['categoryId' => $categoryId]),
            ),
        ]);
    }

    public function results(): View
    {
        $categories = Category::query()
            ->whereHas('pollQuestions', fn (Builder $query): Builder => $query->active())
            ->with(['pollQuestions' => fn (HasMany $query): HasMany => $query
                ->active()
                ->withCount('votes')
                ->with(['answers' => fn (HasMany $answers): HasMany => $answers->withCount('votes')])])
            ->get();

        return view('polls.results', [
            'categories' => $categories,
            'metadata' => $this->metadata->set(
                title: 'Umfrageergebnisse',
                canonical: route('polls.results.get'),
            ),
        ]);
    }
}
