# Laravel Fullname

[![Latest Version on Packagist](https://img.shields.io/packagist/v/plin-code/laravel-full-name.svg?style=flat-square)](https://packagist.org/packages/plin-code/laravel-full-name)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/plin-code/laravel-full-name/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/plin-code/laravel-full-name/actions/workflows/run-tests.yml)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/plin-code/laravel-full-name/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/plin-code/laravel-full-name/actions/workflows/fix-php-code-style-issues.yml)
[![PHPStan Action Status](https://img.shields.io/github/actions/workflow/status/plin-code/laravel-full-name/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/plin-code/laravel-full-name/actions/workflows/phpstan.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/plin-code/laravel-full-name.svg?style=flat-square)](https://packagist.org/packages/plin-code/laravel-full-name)

Search and sort Eloquent queries (and Filament tables) by a person's full name stored across two columns (`first_name` and `last_name`), either on the main model or on a `BelongsTo` / `HasOne` relation.

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

### Filament, via `HasOne`

```php
// User hasOne(Hiker::class) — search and sort users by their hiker full name.
TextColumn::make('hiker.full_name')
    ->fullNameSearchable(relation: 'hiker')
    ->fullNameSortable(relation: 'hiker');
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

The complete API surface lives in [docs/api.md](docs/api.md).

## Performance considerations

The matching strategy uses `LOWER(CONCAT(COALESCE(first, ''), ' ', COALESCE(last, '')))` which prevents btree indexes from being used on `first_name` or `last_name`. On tables up to a few hundred thousand rows this is typically acceptable for admin panel search. For very large tables, pair this package with a dedicated search engine (Meilisearch, Scout, Algolia) and use this package only for sort.

## Matching behavior

The core uses `LOWER(CONCAT(COALESCE(first, ''), ' ', COALESCE(last, '')))` matched with `LIKE ? ESCAPE '!'` in both forward and reversed concatenation forms.

| Query | Record | Matches |
|---|---|---|
| `mario` | first_name=`'Mario'`, last_name=`'Rossi'` | yes |
| `rossi` | first_name=`'Mario'`, last_name=`'Rossi'` | yes |
| `mario rossi` | first_name=`'Mario'`, last_name=`'Rossi'` | yes |
| `rossi mario` | first_name=`'Mario'`, last_name=`'Rossi'` | yes |
| `maria` | first_name=`'Marianna'`, last_name=`'Rossi'` | yes (substring, single token) |
| `maria rossi` | first_name=`'Marianna'`, last_name=`'Rossi'` | no (multi token) |
| `marianna rossi` | first_name=`'Marianna'`, last_name=`'Rossi'` | yes |
| `mario giovanni rossi` | first_name=`'Mario Giovanni'`, last_name=`'Rossi'` | yes |
| `rossi mario giovanni` | first_name=`'Mario Giovanni'`, last_name=`'Rossi'` | yes |
| `bianchi mario` | first_name=`'Mario'`, last_name=`'Rossi Bianchi'` | yes |

The asymmetry between single token and multi token queries is intentional and emerges from the SQL pattern. Single token queries use substring match, so `maria` matches records containing `maria` anywhere in either column. Multi token queries require the tokens to appear contiguously with the separating space between them in the concatenated `first last` or `last first` form, so `maria rossi` matches `Maria Rossi` but not `Marianna Rossi` (the separator space is not present between `maria` and `rossi` in the concatenation). Single token queries are exploratory (the user may be typing a prefix), multi token queries target a specific person.

See [docs/conventions.md](docs/conventions.md) for the rationale behind the naming split between the Eloquent and Filament layers.

## Limitations

1. Only the `BelongsTo` and `HasOne` relation types are supported. `HasMany`, `BelongsToMany`, `MorphTo`, and nested relations raise `UnsupportedRelationException` at query build time.
2. Accent and diacritic normalization is delegated to the database collation. On MySQL, use `utf8mb4_unicode_ci` or `utf8mb4_0900_ai_ci`. On PostgreSQL, consider the `unaccent` extension if needed.
3. Fuzzy matching (soundex, metaphone, Levenshtein, trigram) is out of scope.
4. Single column full name (one `name` column) is not handled. Filament's native `->searchable(['name'])` covers that case already.
5. Empty or whitespace only search input leaves the query unchanged (no `WHERE` clause is added).
6. When combining `orderByFullName(relation: ...)` with an explicit `->select([...])` on the main query, qualify the column names with the main table name (for example `->select(['test_bookings.id'])` rather than `->select(['id'])`). The package performs a `joinSub` under the hood, which can introduce ambiguity for unqualified columns that exist on both tables.

## Testing

```bash
composer test
composer analyse
composer format
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Credits

- [Daniele Barbaro](https://github.com/plin-code)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
