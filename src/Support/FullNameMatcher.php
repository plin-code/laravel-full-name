<?php

namespace PlinCode\LaravelFullName\Support;

use Illuminate\Database\Eloquent\Builder;

final class FullNameMatcher
{
    public const LIKE_ESCAPE_CHAR = '!';

    public static function applySearch(
        Builder $query,
        string $input,
        FullNameOptions $options,
    ): Builder {
        $normalized = self::normalize($input);

        if ($normalized === '') {
            return $query;
        }

        $escaped = self::escapeLike($normalized);
        $pattern = '%'.$escaped.'%';

        return $query->where(function (Builder $sub) use ($pattern, $options) {
            $first = $options->firstNameColumn;
            $last = $options->lastNameColumn;

            $sub->whereRaw(
                "LOWER(CONCAT(COALESCE({$first}, ''), ' ', COALESCE({$last}, ''))) LIKE ? ESCAPE '!'",
                [$pattern],
            )->orWhereRaw(
                "LOWER(CONCAT(COALESCE({$last}, ''), ' ', COALESCE({$first}, ''))) LIKE ? ESCAPE '!'",
                [$pattern],
            );
        });
    }

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

    public static function escapeLike(string $value): string
    {
        $escape = self::LIKE_ESCAPE_CHAR;

        return str_replace(
            [$escape, '%', '_'],
            [$escape.$escape, $escape.'%', $escape.'_'],
            $value,
        );
    }
}
