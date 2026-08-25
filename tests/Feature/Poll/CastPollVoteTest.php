<?php

namespace Tests\Feature\Poll;

use App\Livewire\Poll\PollWidget;
use App\Models\Category;
use App\Models\PollAnswer;
use App\Models\PollQuestion;
use App\Models\PollVote;
use App\Poll\CastPollVote;
use Database\Seeders\ContentDefinitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Cookie as HttpCookie;
use Tests\TestCase;

class CastPollVoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_vote_is_stored_once_per_ip_and_cookie(): void
    {
        [$category, $question, $answer] = $this->pollDefinition();
        $castVote = app(CastPollVote::class);

        $this->assertTrue($castVote->handle($category->id, $question->id, $answer->id, '192.0.2.10', 'cookie-a'));
        $this->assertFalse($castVote->handle($category->id, $question->id, $answer->id, '192.0.2.10', 'cookie-b'));
        $this->assertFalse($castVote->handle($category->id, $question->id, $answer->id, '192.0.2.11', 'cookie-a'));
        $this->assertSame(1, PollVote::query()->count());
    }

    public function test_answer_and_category_mismatches_are_rejected(): void
    {
        [$category, $question] = $this->pollDefinition();
        [$otherCategory, $otherQuestion, $otherAnswer] = $this->pollDefinition();
        $castVote = app(CastPollVote::class);

        try {
            $castVote->handle($category->id, $question->id, $otherAnswer->id, '192.0.2.20', 'cookie-c');
            $this->fail('An answer from another question should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('answer', $exception->errors());
        }

        try {
            $castVote->handle($otherCategory->id, $question->id, $otherQuestion->answers()->firstOrFail()->id, '192.0.2.21', 'cookie-d');
            $this->fail('A question from another category should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('question', $exception->errors());
        }

        $this->assertDatabaseCount('poll_votes', 0);
    }

    public function test_widget_queues_one_year_identity_cookie_and_zero_vote_percentages_are_safe(): void
    {
        [$category, $question, $answer] = $this->pollDefinition();

        $this->assertSame(0.0, $answer->votePercentage(0));

        Livewire::test(PollWidget::class, ['categoryId' => $category->id])
            ->set('selectedAnswerId', $answer->id)
            ->call('vote')
            ->assertSet('hasVoted', true);

        Cookie::flushQueuedCookies();
        $cookieToken = new ReflectionMethod(PollWidget::class, 'cookieToken');
        $cookieToken->invoke(new PollWidget);
        $cookie = collect(Cookie::getQueuedCookies())->first(
            fn (HttpCookie $cookie): bool => $cookie->getName() === 'poll_token',
        );

        $this->assertNotNull($cookie);
        $this->assertGreaterThan(now()->addMonths(11)->getTimestamp(), $cookie->getExpiresTime());

        $this->assertDatabaseHas('poll_votes', [
            'poll_question_id' => $question->id,
            'poll_answer_id' => $answer->id,
        ]);
    }

    public function test_public_poll_pages_render_seeded_definitions(): void
    {
        $this->seed(ContentDefinitionSeeder::class);

        $this->get(route('polls.category.get', ['categoryId' => 1]))
            ->assertOk()
            ->assertSee('Tellertouren-Umfrage');
        $this->get(route('polls.results.get'))
            ->assertOk()
            ->assertSee('Umfrageergebnisse')
            ->assertSee('Was ist dir beim Essen gehen am wichtigsten?');
        $this->get('/umfrage/9999')->assertNotFound();
    }

    public function test_vote_factory_builds_an_answer_for_its_question(): void
    {
        $vote = PollVote::factory()->create();

        $this->assertSame($vote->poll_question_id, $vote->answer->poll_question_id);
    }

    /** @return array{Category, PollQuestion, PollAnswer} */
    private function pollDefinition(): array
    {
        $category = Category::factory()->create();
        $question = PollQuestion::factory()->for($category)->create();
        $answer = PollAnswer::factory()->for($question, 'question')->create();

        return [$category, $question, $answer];
    }
}
