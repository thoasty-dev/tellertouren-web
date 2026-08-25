<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minigame_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('question_text');
            $table->string('guess_question_text');
            $table->string('comment')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('minigame_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('minigame_question_id')->constrained()->cascadeOnDelete();
            $table->string('answer_text');
            $table->string('guess_answer_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('minigame_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('secret', 64)->unique();
            $table->string('creator_name');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('minigame_session_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('minigame_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('minigame_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('minigame_answer_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['minigame_session_id', 'minigame_question_id'], 'session_question_unique');
        });

        Schema::create('minigame_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('minigame_session_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('cookie_token', 64)->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('minigame_participant_guesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('minigame_participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('minigame_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('minigame_answer_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
            $table->unique(['minigame_participant_id', 'minigame_question_id'], 'participant_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minigame_participant_guesses');
        Schema::dropIfExists('minigame_participants');
        Schema::dropIfExists('minigame_session_answers');
        Schema::dropIfExists('minigame_sessions');
        Schema::dropIfExists('minigame_answers');
        Schema::dropIfExists('minigame_questions');
    }
};
