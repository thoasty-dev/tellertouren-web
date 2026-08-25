<?php

namespace App\Blog;

use Hashids\Hashids;

class LegacyArticleIdCodec
{
    private readonly Hashids $hashids;

    public function __construct()
    {
        $this->hashids = new Hashids(
            salt: 'CmBeNS2pFnsxMXgVgQrV6xU2sQztSzPe',
            minHashLength: 6,
            alphabet: 'abcdefghijklmnopqrstuvwxyz1234567890',
        );
    }

    public function encode(int $articleId): string
    {
        return $this->hashids->encode($articleId);
    }

    public function decode(string $hash): ?int
    {
        $decoded = $this->hashids->decode($hash);

        return count($decoded) === 1 && is_int($decoded[0]) ? $decoded[0] : null;
    }
}
