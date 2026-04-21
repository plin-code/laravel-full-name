# laravel-full-name

[![Latest Version on Packagist](https://img.shields.io/packagist/v/plin-code/laravel-full-name.svg?style=flat-square)](https://packagist.org/packages/plin-code/laravel-full-name)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/plin-code/laravel-full-name/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/plin-code/laravel-full-name/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/plin-code/laravel-full-name/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/plin-code/laravel-full-name/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/plin-code/laravel-full-name.svg?style=flat-square)](https://packagist.org/packages/plin-code/laravel-full-name)

Search and sort Eloquent queries (and Filament tables) by a person's full name stored across two columns (`first_name` and `last_name`), either on the main model or on a `BelongsTo` relation.

## What it solves

Filament's built-in `->searchable(['first_name', 'last_name'])` searches each column independently, which fails for composite queries like `"mario rossi"`. The native `->sortable(query: ...)` works for direct columns but requires repetitive custom join logic for relation-based sort. This package encapsulates the solution once, tested once, documented once.

## Installation

```bash
composer require plin-code/laravel-full-name
```

No config file, no migrations, no Blade views, no Artisan command. The service provider auto-registers.

## Requirements

- PHP 8.4
- Laravel 12 or 13
- Filament 4 or 5 (optional, only needed for the Filament layer)
- MySQL 8, PostgreSQL 14+, or SQLite 3

## Quick start

### Standalone Eloquent

```php
Booking::query()
    ->searchFullName($request->input('q'))
    ->orderByFullName('asc')
    ->paginate();

Booking::query()
    ->searchFullName($request->input('q'), relation: 'user')
    ->orderByFullName('asc', relation: 'user')
    ->paginate();
```

### Filament, direct columns

```php
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->fullNameSearchable()
    ->fullNameSortable();
```

### Filament, via `BelongsTo`

```php
TextColumn::make('user.full_name')
    ->fullNameSearchable(relation: 'user')
    ->fullNameSortable(relation: 'user');
```

### Custom column names

```php
TextColumn::make('full_name')
    ->fullNameSearchable(
        firstNameColumn: 'given_name',
        lastNameColumn: 'family_name',
    )
    ->fullNameSortable(
        firstNameColumn: 'given_name',
        lastNameColumn: 'family_name',
    );
```

## API reference

### `Builder::searchFullName(string $search, ?string $relation = null, string $firstNameColumn = 'first_name', string $lastNameColumn = 'last_name'): Builder`

Applies the full name search filter to the query. Returns the query for chaining.

### `Builder::orderByFullName(string $direction = 'asc', ?string $relation = null, string $firstNameColumn = 'first_name', string $lastNameColumn = 'last_name'): Builder`

Applies a multi column sort (last name, then first name) in the given direction. When a relation is provided, performs a `joinSub` against the related model's own query to respect global scopes (such as `SoftDeletes`).

### `TextColumn::fullNameSearchable(?string $relation = null, string $firstNameColumn = 'first_name', string $lastNameColumn = 'last_name'): static`

Registers a Filament searchable query callback that delegates to `searchFullName`.

### `TextColumn::fullNameSortable(?string $relation = null, string $firstNameColumn = 'first_name', string $lastNameColumn = 'last_name'): static`

Registers a Filament sortable query callback that delegates to `orderByFullName`.

## Matching behavior

The core uses `LOWER(CONCAT(COALESCE(first, ''), ' ', COALESCE(last, '')))` matched with `LIKE ? ESCAPE '!'` in both forward and reversed concatenation forms.

| Query | Record | Matches |
|---|---|---|
| `mario` | first_name=`'Mario'`, last_name=`'Rossi'` | yes |
| `rossi` | first_name=`'Mario'`, last_name=`'Rossi'` | yes |
| `mario rossi` | first_name=`'Mario'`, last_name=`'Rossi'` | yes |
| `rossi mario` | first_name=`'Mario'`, last_name=`'Rossi'` | yes |
| `maria` | first_name=`'Mariacarmela'`, last_name=`'Rossi'` | yes (substring, single token) |
| `maria rossi` | first_name=`'Mariacarmela'`, last_name=`'Rossi'` | no (word boundary, multi token) |
| `mariacarmela rossi` | first_name=`'Mariacarmela'`, last_name=`'Rossi'` | yes |
| `mario giovanni rossi` | first_name=`'Mario Giovanni'`, last_name=`'Rossi'` | yes |
| `rossi mario giovanni` | first_name=`'Mario Giovanni'`, last_name=`'Rossi'` | yes |
| `bianchi mario` | first_name=`'Mario'`, last_name=`'Rossi Bianchi'` | yes |

The asymmetry between single token (substring) and multi token (word boundary) matching is intentional. Single token queries are exploratory (the user may be typing a prefix), multi token queries target a specific person.

## Limitations

1. Only the `BelongsTo` relation type is supported in v1. `HasOne`, `HasMany`, `BelongsToMany`, `MorphTo`, and nested relations raise `UnsupportedRelationException` at query build time.
2. Accent and diacritic normalization is delegated to the database collation. On MySQL, use `utf8mb4_unicode_ci` or `utf8mb4_0900_ai_ci`. On PostgreSQL, consider the `unaccent` extension if needed.
3. Large tables may experience slow search because `LOWER(CONCAT(...))` cannot use a btree index. For very large tables, pair this package with a dedicated search engine (Meilisearch, Scout, Algolia).
4. Fuzzy matching (soundex, metaphone, Levenshtein, trigram) is out of scope.
5. Single column full name (one `name` column) is not handled. Filament's native `->searchable(['name'])` covers that case already.
6. Empty or whitespace only search input leaves the query unchanged (no `WHERE` clause is added).

## Testing

```bash
composer test
composer analyse
composer format
```

The main suite runs against SQLite in memory. Cross driver integration tests (MySQL and PostgreSQL) run in CI against service containers.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Credits

- [Daniele Barbaro](https://github.com/plin-code)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
