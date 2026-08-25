<?php

namespace App\Models;

use Database\Factories\PollAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollAnswer extends Model
{
    /** @use HasFactory<PollAnswerFactory> */
    use HasFactory;

    protected $fillable = ['poll_question_id', 'answer_text', 'sort_order'];

    protected $attributes = ['sort_order' => 0];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(PollQuestion::class, 'poll_question_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function voteCount(): int
    {
        return isset($this->votes_count) ? (int) $this->votes_count : $this->votes()->count();
    }

    public function votePercentage(int $totalVotes): float
    {
        return $totalVotes === 0 ? 0.0 : round(($this->voteCount() / $totalVotes) * 100, 1);
    }
}
