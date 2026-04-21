<?php

namespace PlinCode\LaravelFullName\Support;

final class FullNameMatcher
{
    public const LIKE_ESCAPE_CHAR = '!';

    public static function normalize(string $input): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', $input);

        if ($collapsed === null) {
            throw new \RuntimeException(
                'Failed to normalize input: preg_replace returned null (PCRE error code '.preg_last_error().').'
            );
        }

        $trimmed = trim($collapsed);

        return mb_strtolower($trimmed, 'UTF-8');
    }

    public static function escapeLike(string $normalized): string
    {
        return str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            $normalized,
        );
    }
}
