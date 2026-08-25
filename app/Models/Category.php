<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function pollQuestions(): HasMany
    {
        return $this->hasMany(PollQuestion::class);
    }

    public function minigameQuestions(): HasMany
    {
        return $this->hasMany(MinigameQuestion::class);
    }
}
