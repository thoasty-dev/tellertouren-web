<?php

namespace Tests\Feature\Database;

use App\Models\MinigameParticipant;
use App\Models\MinigameSession;
use App\Models\PollVote;
use Database\Seeders\ContentDefinitionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_database_contains_only_retained_runtime_domains_and_support_tables(): void
    {
        foreach ([
            'laravel_jobs', 'laravel_failed_jobs', 'laravel_sessions', 'laravel_cache',
            'laravel_cache_locks', 'laravel_job_batches', 'categories', 'poll_questions',
            'poll_answers', 'poll_votes', 'minigame_questions', 'minigame_answers',
            'minigame_sessions', 'minigame_session_answers', 'minigame_participants',
            'minigame_participant_guesses',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected {$table} to exist.");
        }

        foreach ([
            'users', 'articles', 'article_translations', 'pictures', 'media', 'tags',
            'picture_to_article', 'article_to_tag',
        ] as $table) {
            $this->assertFalse(Schema::hasTable($table), "Expected {$table} to be absent.");
        }

        $this->assertFalse(Schema::hasColumn('categories', 'article_id'));

        $pollVoteIndexes = collect(Schema::getIndexes('poll_votes'))->pluck('name');
        $this->assertContains('poll_question_ip_unique', $pollVoteIndexes);
        $this->assertContains('poll_question_cookie_unique', $pollVoteIndexes);
    }

    public function test_definition_seeder_is_idempotent_and_preserves_activity(): void
    {
        $this->seed(ContentDefinitionSeeder::class);

        $vote = PollVote::query()->create([
            'poll_question_id' => 1,
            'poll_answer_id' => 1,
            'ip_address' => '192.0.2.1',
            'cookie_token' => str_repeat('a', 64),
        ]);
        $session = MinigameSession::query()->create([
            'category_id' => 1,
            'secret' => str_repeat('b', 32),
            'creator_name' => 'Thorsten',
        ]);
        $participant = MinigameParticipant::query()->create([
            'minigame_session_id' => $session->id,
            'name' => 'Gast',
            'cookie_token' => str_repeat('c', 64),
        ]);

        $definitionCounts = [
            'categories' => 3,
            'poll_questions' => 16,
            'poll_answers' => 75,
            'minigame_questions' => 24,
            'minigame_answers' => 96,
        ];

        $this->seed(ContentDefinitionSeeder::class);

        foreach ($definitionCounts as $table => $count) {
            $this->assertSame($count, DB::table($table)->count());
        }

        $this->assertTrue($vote->fresh()->exists);
        $this->assertTrue($session->fresh()->exists);
        $this->assertTrue($participant->fresh()->exists);
    }

    public function test_forward_migrations_upgrade_a_legacy_database_without_overwriting_activity(): void
    {
        $databasePath = tempnam(sys_get_temp_dir(), 'tellertouren-legacy-');
        $this->assertIsString($databasePath);

        Config::set('database.connections.legacy_test', [
            'driver' => 'sqlite',
            'database' => $databasePath,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        $schema = Schema::connection('legacy_test');
        $connection = DB::connection('legacy_test');

        try {
            $schema->create('migrations', function (Blueprint $table): void {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
            });
            $schema->create('laravel_jobs', function (Blueprint $table): void {
                $table->id();
                $table->longText('payload')->nullable();
            });
            $schema->create('laravel_failed_jobs', function (Blueprint $table): void {
                $table->id();
            });
            $schema->create('categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('article_id')->nullable();
            });
            $schema->create('poll_votes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('poll_question_id');
                $table->unsignedBigInteger('poll_answer_id');
                $table->string('ip_address', 45)->nullable();
                $table->string('cookie_token', 64)->nullable();
            });

            foreach ([
                'poll_questions', 'poll_answers', 'minigame_questions', 'minigame_answers',
                'minigame_sessions', 'minigame_session_answers', 'minigame_participants',
                'minigame_participant_guesses', 'picture_to_article', 'article_to_tag',
                'article_metas', 'tag_metas', 'article_translations', 'pictures', 'tags',
                'media', 'articles', 'users', 'newsletter_subscribers',
            ] as $tableName) {
                $schema->create($tableName, function (Blueprint $table): void {
                    $table->id();
                });
            }

            $connection->table('migrations')->insert(collect([
                '2024_01_03_123241_create_laravel_jobs_table',
                '2024_01_03_123247_create_laravel_failed_jobs_table',
                '2024_07_21_113627_create_categories_table',
                '2025_12_11_192229_create_poll_tables',
                '2025_12_11_202119_create_minigame_tables',
            ])->map(fn (string $migration): array => ['migration' => $migration, 'batch' => 1])->all());
            $connection->table('laravel_jobs')->insert(['id' => 10, 'payload' => 'existing job']);
            $connection->table('categories')->insert(['id' => 1, 'name' => 'Restaurants', 'article_id' => 1]);
            $connection->table('poll_votes')->insert([
                'id' => 20,
                'poll_question_id' => 1,
                'poll_answer_id' => 1,
                'ip_address' => '192.0.2.30',
                'cookie_token' => str_repeat('d', 64),
            ]);

            $exitCode = Artisan::call('migrate', [
                '--database' => 'legacy_test',
                '--force' => true,
                '--no-interaction' => true,
            ]);

            $this->assertSame(0, $exitCode, Artisan::output());
            $this->assertTrue($schema->hasTable('laravel_sessions'));
            $this->assertTrue($schema->hasTable('laravel_cache'));
            $this->assertTrue($schema->hasTable('laravel_cache_locks'));
            $this->assertTrue($schema->hasTable('laravel_job_batches'));
            $this->assertSame('existing job', $connection->table('laravel_jobs')->where('id', 10)->value('payload'));
            $this->assertTrue($connection->table('poll_votes')->where('id', 20)->exists());
            $this->assertFalse($schema->hasColumn('categories', 'article_id'));
            $this->assertTrue($schema->hasTable('users'));
            $this->assertTrue($schema->hasTable('newsletter_subscribers'));

            foreach (['articles', 'article_translations', 'pictures', 'tags', 'media'] as $removedTable) {
                $this->assertFalse($schema->hasTable($removedTable));
            }
        } finally {
            DB::disconnect('legacy_test');
            unlink($databasePath);
        }
    }
}
