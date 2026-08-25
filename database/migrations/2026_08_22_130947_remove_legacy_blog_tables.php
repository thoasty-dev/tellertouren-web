<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('picture_to_article');
        Schema::dropIfExists('article_to_tag');
        Schema::dropIfExists('article_metas');
        Schema::dropIfExists('tag_metas');
        Schema::dropIfExists('article_translations');
        Schema::dropIfExists('pictures');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('media');

        if (Schema::hasColumn('categories', 'article_id')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->dropColumn('article_id');
            });
        }

        Schema::dropIfExists('articles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('The static blog migration is intentionally irreversible; restore the legacy database from backup instead.');
    }
};
