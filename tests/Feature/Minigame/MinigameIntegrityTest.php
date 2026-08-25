<?php

namespace Tests\Feature\Minigame;

use App\Livewire\Minigame\MinigameCreatorSession;
use App\Livewire\Minigame\MinigameParticipantSession;
use App\Minigame\SaveCreatorAnswer;
use App\Minigame\SaveParticipantGuess;
use App\Models\Category;
use App\Models\MinigameAnswer;
use App\Models\MinigameParticipant;
use App\Models\MinigameParticipantGuess;
use App\Models\MinigameQuestion;
use App\Models\MinigameSession;
use App\Models\MinigameSessionAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class MinigameIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_answers_and_participant_guesses_are_scoped_to_the_session(): void
    {
        [$category, $question, $correctAnswer] = $this->definition();
        $session = MinigameSession::factory()->for($category)->create();

        app(SaveCreatorAnswer::class)->handle($session->id, $category->id, $question->id, $correctAnswer->id);
        $this->assertDatabaseHas('minigame_session_answers', [
            'minigame_session_id' => $session->id,
            'minigame_question_id' => $question->id,
            'minigame_answer_id' => $correctAnswer->id,
        ]);

        $session->update(['completed_at' => now()]);
        $participant = MinigameParticipant::factory()->for($session, 'session')->create(['cookie_token' => 'participant-token']);

        app(SaveParticipantGuess::class)->handle(
            $participant->id,
            $session->id,
            $question->id,
            $correctAnswer->id,
            'participant-token',
        );

        $this->assertDatabaseHas('minigame_participant_guesses', [
            'minigame_participant_id' => $participant->id,
            'minigame_question_id' => $question->id,
            'is_correct' => true,
        ]);
    }

    public function test_cross_category_answers_sessions_and_tokens_are_rejected(): void
    {
        [$category, $question, $answer] = $this->definition();
        [$otherCategory, $otherQuestion, $otherAnswer] = $this->definition();
        $session = MinigameSession::factory()->for($category)->create();

        $this->expectValidationError('answer', function () use ($session, $category, $question, $otherAnswer): void {
            app(SaveCreatorAnswer::class)->handle($session->id, $category->id, $question->id, $otherAnswer->id);
        });

        $this->expectValidationError('session', function () use ($session, $otherCategory, $question, $answer): void {
            app(SaveCreatorAnswer::class)->handle($session->id, $otherCategory->id, $question->id, $answer->id);
        });

        app(SaveCreatorAnswer::class)->handle($session->id, $category->id, $question->id, $answer->id);
        $session->update(['completed_at' => now()]);
        $participant = MinigameParticipant::factory()->for($session, 'session')->create(['cookie_token' => 'correct-token']);

        $this->expectValidationError('participant', function () use ($participant, $session, $question, $answer): void {
            app(SaveParticipantGuess::class)->handle($participant->id, $session->id, $question->id, $answer->id, 'wrong-token');
        });

        $this->expectValidationError('question', function () use ($participant, $session, $otherQuestion, $otherAnswer): void {
            app(SaveParticipantGuess::class)->handle($participant->id, $session->id, $otherQuestion->id, $otherAnswer->id, 'correct-token');
        });
    }

    public function test_session_can_be_created_resumed_played_and_completed(): void
    {
        [$category, $question, $answer] = $this->definition();

        Livewire::test(MinigameCreatorSession::class, ['categoryId' => $category->id])
            ->set('creatorName', 'Thorsten')
            ->call('startGame');

        $session = MinigameSession::query()->sole();
        $this->assertSame(32, strlen($session->secret));

        Livewire::test(MinigameCreatorSession::class, [
            'categoryId' => $category->id,
            'secret' => $session->secret,
        ])
            ->assertSet('sessionId', $session->id)
            ->set("selectedAnswers.{$question->id}", $answer->id)
            ->call('saveAnswer', $question->id)
            ->call('completeQuestions')
            ->assertSet('currentStep', 3);

        $this->get(route('minigame.session', ['categoryId' => $category->id, 'secret' => $session->secret]))->assertOk();
        $this->get(route('minigame.session', ['categoryId' => $category->id + 1, 'secret' => $session->secret]))->assertNotFound();
        $this->get(route('minigame.play', ['secret' => $session->secret]))->assertOk();

        Livewire::withCookie('minigame_participant_token', 'participant-cookie')
            ->test(MinigameParticipantSession::class, ['secret' => $session->secret])
            ->set('participantName', 'Gast')
            ->call('startPlaying')
            ->set("selectedAnswers.{$question->id}", $answer->id)
            ->call('saveGuess', $question->id)
            ->call('completeQuestions')
            ->assertSet('currentStep', 3)
            ->assertSee('100 %');

        $this->assertDatabaseHas('minigame_participants', [
            'minigame_session_id' => $session->id,
            'name' => 'Gast',
            'cookie_token' => 'participant-cookie',
        ]);
        $this->assertDatabaseHas('minigame_participant_guesses', [
            'minigame_question_id' => $question->id,
            'minigame_answer_id' => $answer->id,
            'is_correct' => true,
        ]);
    }

    public function test_factories_keep_questions_answers_and_sessions_in_the_same_scope(): void
    {
        $sessionAnswer = MinigameSessionAnswer::factory()->create();
        $participantGuess = MinigameParticipantGuess::factory()->create();

        $this->assertSame($sessionAnswer->session->category_id, $sessionAnswer->question->category_id);
        $this->assertSame($sessionAnswer->question->id, $sessionAnswer->answer->minigame_question_id);
        $this->assertSame($participantGuess->participant->session->category_id, $participantGuess->question->category_id);
        $this->assertSame($participantGuess->question->id, $participantGuess->answer->minigame_question_id);
    }

    /** @return array{Category, MinigameQuestion, MinigameAnswer} */
    private function definition(): array
    {
        $category = Category::factory()->create();
        $question = MinigameQuestion::factory()->for($category)->create();
        $answer = MinigameAnswer::factory()->for($question, 'question')->create();

        return [$category, $question, $answer];
    }

    private function expectValidationError(string $field, callable $operation): void
    {
        try {
            $operation();
            $this->fail("Expected validation error for {$field}.");
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }
}
