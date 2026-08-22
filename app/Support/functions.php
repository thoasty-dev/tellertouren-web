<?php

/**
 * Generates a v4 universally unique identifier (UUID), optionally with a prefix.
 *
 * @param string|null $prefix
 * @return string
 */
function uuid (?string $prefix = null): string {
    return ($prefix ? $prefix . '-' : '') . \Illuminate\Support\Str::uuid();
}
