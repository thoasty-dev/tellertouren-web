<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MinigameSession;
use App\Support\PageMetadata;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class MinigameController extends Controller
{
    public function __construct(private readonly PageMetadata $metadata) {}

    public function index(): View
    {
        $categories = Category::query()
            ->whereHas('minigameQuestions', fn (Builder $query): Builder => $query->active())
            ->get();

        return view('minigame.index', [
            'categories' => $categories,
            'metadata' => $this->metadata->set(
                title: 'Wie gut kennen dich deine Freunde?',
                description: 'Erstelle dein Tellertouren-Minispiel und finde heraus, wie gut deine Freunde dich kennen.',
                canonical: route('minigame.index'),
            ),
        ]);
    }

    public function category(int $categoryId): View
    {
        $category = Category::query()
            ->whereHas('minigameQuestions', fn (Builder $query): Builder => $query->active())
            ->findOrFail($categoryId);

        return view('minigame.category', [
            'categoryId' => $categoryId,
            'category' => $category,
            'secret' => null,
            'metadata' => $this->metadata->set(
                title: 'Minigame: '.$category->name,
                canonical: route('minigame.category', ['categoryId' => $categoryId]),
            ),
        ]);
    }

    public function session(int $categoryId, string $secret): View
    {
        $session = MinigameSession::query()
            ->with('category')
            ->where('category_id', $categoryId)
            ->bySecret($secret)
            ->firstOrFail();

        return view('minigame.category', [
            'categoryId' => $categoryId,
            'category' => $session->category,
            'secret' => $secret,
            'metadata' => $this->metadata->set(
                title: 'Minigame von '.$session->creator_name,
                canonical: route('minigame.session', ['categoryId' => $categoryId, 'secret' => $secret]),
            ),
        ]);
    }

    public function play(string $secret): View
    {
        $session = MinigameSession::query()->with('category')->bySecret($secret)->firstOrFail();

        abort_unless($session->isCompleted(), 404);

        return view('minigame.play', [
            'session' => $session,
            'secret' => $secret,
            'metadata' => $this->metadata->set(
                title: 'Wie gut kennst du '.$session->creator_name.'?',
                description: 'Beantworte das Tellertouren-Minispiel und finde heraus, wie gut du '.$session->creator_name.' kennst.',
                canonical: route('minigame.play', ['secret' => $secret]),
            ),
        ]);
    }
}
