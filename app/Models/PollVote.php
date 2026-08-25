<?php

namespace App\Models;

use Database\Factories\PollVoteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    /** @use HasFactory<PollVoteFactory> */
    use HasFactory;

    protected $fillable = ['poll_question_id', 'poll_answer_id', 'ip_address', 'cookie_token'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(PollQuestion::class, 'poll_question_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(PollAnswer::class, 'poll_answer_id');
    }

    public function scopeForQuestion(Builder $query, int $questionId): Builder
    {
        return $query->where('poll_question_id', $questionId);
    }

    public function scopeByIpOrCookie(Builder $query, ?string $ip, ?string $cookieToken): Builder
    {
        return $query->where(function (Builder $identities) use ($ip, $cookieToken): void {
            if (filled($ip)) {
                $identities->where('ip_address', $ip);
            }

            if (filled($cookieToken)) {
                $method = filled($ip) ? 'orWhere' : 'where';
                $identities->{$method}('cookie_token', $cookieToken);
            }
        });
    }
}
