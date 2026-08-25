<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('question_text');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('poll_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_question_id')->constrained()->cascadeOnDelete();
            $table->string('answer_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('poll_votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poll_answer_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('cookie_token', 64)->nullable();
            $table->timestamps();
            $table->index(['ip_address', 'poll_question_id']);
            $table->index(['cookie_token', 'poll_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_answers');
        Schema::dropIfExists('poll_questions');
    }
};
