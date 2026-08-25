<?php

namespace App\Models;

use Database\Factories\MinigameSessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinigameSession extends Model
{
    /** @use HasFactory<MinigameSessionFactory> */
    use HasFactory;

    protected $fillable = ['category_id', 'secret', 'creator_name', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function scopeBySecret(Builder $query, string $secret): Builder
    {
        return $query->where('secret', $secret);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sessionAnswers(): HasMany
    {
        return $this->hasMany(MinigameSessionAnswer::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MinigameParticipant::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public static function generateSecret(): string
    {
        return bin2hex(random_bytes(16));
    }
}
