# Changelog

All notable changes to `laravel-full-name` will be documented in this file.

## v1.1.0 - 2026-04-28

### Added

- `HasOne` relation support for both `searchFullName` (via `whereHas`) and `orderByFullName` (via `joinSub`). Join keys are inferred from the relation type: `foreignKey = ownerKey` for `BelongsTo`, `localKey = foreignKey` for `HasOne`.
- New `Account` test fixture and `EloquentSearchHasOneRelationTest` / `EloquentSortHasOneRelationTest` covering search, sort, soft deletes, idempotent joins, and row deduplication.

### Changed

- `UnsupportedRelationException::forRelationType` message now reads `must be BelongsTo or HasOne` to reflect the expanded relation support.

## v1.0.0 - 2026-04-21

Initial release.

### Added

- `searchFullName` macro on `Illuminate\Database\Eloquent\Builder` for full name search across `first_name` and `last_name` columns.
- `orderByFullName` macro on `Illuminate\Database\Eloquent\Builder` for sorting by last name then first name.
- `fullNameSearchable` and `fullNameSortable` macros on `Filament\Tables\Columns\TextColumn` that delegate to the Eloquent layer. Active only when Filament is installed.
- `BelongsTo` relation support for both search (via `whereHas`) and sort (via `joinSub` that preserves global scopes, including `SoftDeletes`).
- Multi-token matching with forward and reversed concatenation variants. Substring match for single-token queries, contiguous match for multi-token queries.
- NULL-safe matching via `COALESCE` on the concatenated columns.
- Cross-driver support for MySQL, PostgreSQL, SQLite.
- `UnsupportedRelationException` raised for non-`BelongsTo` or missing relations.
- `InvalidSortDirectionException` raised when the sort direction is not `asc` or `desc`.
- Pest 4 test suite covering unit, feature, and cross-driver integration scenarios (77 tests total).
- PHPStan level 7 configuration with Larastan.
- Rector configuration targeting PHP 8.4.
- GitHub Actions workflows for tests, integration (MySQL + PostgreSQL), PHPStan, Pint auto-fix, and Rector dry-run.
- API reference at `docs/api.md` and naming conventions rationale at `docs/conventions.md`.

### Requirements

- PHP 8.4
- Laravel 12 or 13
- Filament 4 or 5 (optional, only needed for the Filament layer)

### Installation

```bash
composer require plin-code/laravel-full-name
```

## 1.0.0 - 2026-04-21

Initial release.

### Added

- `searchFullName` macro on `Illuminate\Database\Eloquent\Builder` for full name search across `first_name` and `last_name` columns.
- `orderByFullName` macro on `Illuminate\Database\Eloquent\Builder` for sorting by last name then first name.
- `fullNameSearchable` and `fullNameSortable` macros on `Filament\Tables\Columns\TextColumn` that delegate to the Eloquent layer. Active only when Filament is installed.
- `BelongsTo` relation support for both search (via `whereHas`) and sort (via `joinSub` that preserves global scopes, including `SoftDeletes`).
- Multi-token matching with forward and reversed concatenation variants. Substring match for single-token queries, contiguous match for multi-token queries.
- NULL-safe matching via `COALESCE` on the concatenated columns.
- Cross-driver support for MySQL, PostgreSQL, SQLite.
- `UnsupportedRelationException` raised for non-`BelongsTo` or missing relations, with named constructors `forRelationType` and `forMissingRelation`.
- `InvalidSortDirectionException` raised when the sort direction is not `'asc'` or `'desc'`.
- Pest 4 test suite covering unit, feature, and cross-driver integration scenarios (77 tests total, 100 assertions).
- PHPStan level 7 configuration with Larastan and a justified 6-entry baseline.
- Rector configuration targeting PHP 8.4 with the `CODE_QUALITY`, `DEAD_CODE`, `TYPE_DECLARATION`, `EARLY_RETURN` sets.
- GitHub Actions workflows for tests (matrix: PHP 8.4 x Laravel 12/13 x Filament 4/5), cross-driver integration (MySQL 8, PostgreSQL 16), PHPStan, Pint auto-fix, and Rector dry-run.
- API reference at `docs/api.md` and naming conventions rationale at `docs/conventions.md`.
