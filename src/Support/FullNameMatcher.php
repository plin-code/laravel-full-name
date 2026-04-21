<?php

namespace PlinCode\LaravelFullName\Support;

final class FullNameMatcher
{
    public static function normalize(string $input): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', $input) ?? $input;
        $trimmed = trim($collapsed);

        return mb_strtolower($trimmed, 'UTF-8');
    }
}
