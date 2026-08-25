<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ContentDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            'categories',
            'poll_questions',
            'poll_answers',
            'minigame_questions',
            'minigame_answers',
        ] as $table) {
            foreach ($this->rows($table) as $row) {
                $id = $row['id'];
                unset($row['id']);

                DB::table($table)->updateOrInsert(
                    ['id' => $id],
                    [...$row, 'updated_at' => now()],
                );
            }
        }
    }

    /** @return list<array<string, mixed>> */
    private function rows(string $table): array
    {
        $contents = file_get_contents(database_path("seeders/data/{$table}.json"));

        if ($contents === false) {
            throw new RuntimeException("Could not read seed definitions for {$table}.");
        }

        $rows = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($rows)) {
            throw new RuntimeException("Invalid seed definitions for {$table}.");
        }

        return $rows;
    }
}
