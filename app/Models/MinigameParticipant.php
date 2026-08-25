<?php

namespace App\Models;

use Database\Factories\MinigameParticipantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinigameParticipant extends Model
{
    /** @use HasFactory<MinigameParticipantFactory> */
    use HasFactory;

    protected $fillable = ['minigame_session_id', 'name', 'cookie_token', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function scopeByCookieToken(Builder $query, string $cookieToken): Builder
    {
        return $query->where('cookie_token', $cookieToken);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(MinigameSession::class, 'minigame_session_id');
    }

    public function guesses(): HasMany
    {
        return $this->hasMany(MinigameParticipantGuess::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function correctGuessesCount(): int
    {
        return isset($this->correct_guesses_count)
            ? (int) $this->correct_guesses_count
            : $this->guesses()->where('is_correct', true)->count();
    }

    public function totalGuessesCount(): int
    {
        return isset($this->guesses_count) ? (int) $this->guesses_count : $this->guesses()->count();
    }

    public function scorePercentage(): float
    {
        return $this->totalGuessesCount() === 0
            ? 0.0
            : round(($this->correctGuessesCount() / $this->totalGuessesCount()) * 100);
    }
}
