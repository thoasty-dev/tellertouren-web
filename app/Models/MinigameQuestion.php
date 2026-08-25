<?php

namespace App\Models;

use Database\Factories\MinigameQuestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinigameQuestion extends Model
{
    /** @use HasFactory<MinigameQuestionFactory> */
    use HasFactory;

    protected $fillable = ['category_id', 'question_text', 'guess_question_text', 'comment', 'sort_order', 'is_active'];

    protected $attributes = ['sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(MinigameAnswer::class)->orderBy('sort_order');
    }

    public function guessQuestionFor(string $userName): string
    {
        return str_replace(':user', $userName, $this->guess_question_text);
    }
}
