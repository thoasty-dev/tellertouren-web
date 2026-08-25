<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicateIpVote = DB::table('poll_votes')
            ->select('poll_question_id', 'ip_address')
            ->whereNotNull('ip_address')
            ->groupBy('poll_question_id', 'ip_address')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
        $duplicateCookieVote = DB::table('poll_votes')
            ->select('poll_question_id', 'cookie_token')
            ->whereNotNull('cookie_token')
            ->groupBy('poll_question_id', 'cookie_token')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateIpVote || $duplicateCookieVote) {
            throw new RuntimeException('Poll vote uniqueness cannot be added while duplicate identities exist.');
        }

        Schema::table('poll_votes', function (Blueprint $table): void {
            $table->unique(['poll_question_id', 'ip_address'], 'poll_question_ip_unique');
            $table->unique(['poll_question_id', 'cookie_token'], 'poll_question_cookie_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poll_votes', function (Blueprint $table): void {
            $table->dropUnique('poll_question_ip_unique');
            $table->dropUnique('poll_question_cookie_unique');
        });
    }
};
