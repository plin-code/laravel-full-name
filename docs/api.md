# API reference

Complete reference for the four public macros exposed by `plin-code/laravel-full-name` and the exceptions they may raise.

## Eloquent Builder macros

The core layer registers two macros on `Illuminate\Database\Eloquent\Builder`. Both are auto registered by the service provider during `packageBooted()`.

### `searchFullName`

**Signature**

```php
searchFullName(
    string $search,
    ?string $relation = null,
    string $firstNameColumn = 'first_name',
    string $lastNameColumn = 'last_name',
): Builder
```

**Parameters**

- `$search` is the raw user input. Whitespace is collapsed and the whole string is lowercased before being matched.
- `$relation` is an optional `BelongsTo` or `HasOne` relation name on the query's model. When supplied, the search is wrapped in `whereHas`.
- `$firstNameColumn` and `$lastNameColumn` override the default column names on the target table (the main table when `$relation` is null, the related table otherwise).

**Returns**

The same builder for fluent chaining.

**Throws**

- `PlinCode\LaravelFullName\Exceptions\UnsupportedRelationException` when `$relation` is set but the named relation either does not exist on the model or is neither a `BelongsTo` nor a `HasOne`.

**Examples**

Direct columns on the current model.

```php
Person::query()
    ->searchFullName($request->input('q'))
    ->paginate();
```

Via a BelongsTo relation.

```php
Booking::query()
    ->searchFullName($request->input('q'), relation: 'person')
    ->paginate();
```

Via a HasOne relation. Given `User hasOne(Hiker::class)`:

```php
User::query()
    ->searchFullName($request->input('q'), relation: 'hiker')
    ->paginate();
```

With custom column names.

```php
Booking::query()
    ->searchFullName(
        $request->input('q'),
        relation: 'person',
        firstNameColumn: 'given_name',
        lastNameColumn: 'family_name',
    )
    ->paginate();
```

### `orderByFullName`

**Signature**

```php
orderByFullName(
    string $direction = 'asc',
    ?string $relation = null,
    string $firstNameColumn = 'first_name',
    string $lastNameColumn = 'last_name',
): Builder
```

**Parameters**

- `$direction` accepts `'asc'` or `'desc'` (case insensitive, trimmed). Any other value raises `InvalidSortDirectionException`.
- `$relation` is an optional `BelongsTo` or `HasOne` relation name. When supplied, a `joinSub` against the related model's own query is performed, which preserves global scopes such as `SoftDeletes`. The join keys are inferred from the relation type: `foreignKey = ownerKey` for `BelongsTo`, `localKey = foreignKey` for `HasOne`.
- `$firstNameColumn` and `$lastNameColumn` override the default column names.

**Returns**

The same builder for fluent chaining.

**Throws**

- `InvalidSortDirectionException` when `$direction` is neither `'asc'` nor `'desc'`.
- `UnsupportedRelationException` when `$relation` is set but invalid.

**Examples**

Direct columns.

```php
Person::query()
    ->orderByFullName('asc')
    ->paginate();
```

Via relation.

```php
Booking::query()
    ->orderByFullName('desc', relation: 'person')
    ->paginate();
```

Combining search and sort.

```php
Booking::query()
    ->searchFullName($request->input('q'), relation: 'person')
    ->orderByFullName('asc', relation: 'person')
    ->paginate();
```

**Note on explicit selects.** When `$relation` is non null the package performs a `joinSub`. If the caller has already set `->select([...])` on the main query, those selects are preserved. Qualify unqualified column names (`test_bookings.id` rather than `id`) to avoid ambiguous column errors from the joined subquery.

## Filament TextColumn macros

The optional layer registers two macros on `Filament\Tables\Columns\TextColumn`. These macros are only registered if Filament is installed (the service provider checks `class_exists(Filament\Tables\Columns\Column::class)` before activation).

### `fullNameSearchable`

**Signature**

```php
fullNameSearchable(
    ?string $relation = null,
    string $firstNameColumn = 'first_name',
    string $lastNameColumn = 'last_name',
): static
```

Delegates to `Builder::searchFullName`. Same parameters, same exceptions.

**Examples**

```php
TextColumn::make('full_name')
    ->fullNameSearchable();

TextColumn::make('user.full_name')
    ->fullNameSearchable(relation: 'user');

TextColumn::make('full_name')
    ->fullNameSearchable(
        firstNameColumn: 'given_name',
        lastNameColumn: 'family_name',
    );
```

### `fullNameSortable`

**Signature**

```php
fullNameSortable(
    ?string $relation = null,
    string $firstNameColumn = 'first_name',
    string $lastNameColumn = 'last_name',
): static
```

Delegates to `Builder::orderByFullName`. Direction is supplied by Filament at sort time.

**Examples**

```php
TextColumn::make('full_name')
    ->fullNameSortable();

TextColumn::make('user.full_name')
    ->fullNameSortable(relation: 'user');
```

## Exceptions

### `UnsupportedRelationException`

Extends `\InvalidArgumentException`. Two named constructors:

- `UnsupportedRelationException::forMissingRelation(string $relationName, string $modelClass)` raised when the named relation method does not exist on the query's model.
- `UnsupportedRelationException::forRelationType(string $relationName, string $modelClass, string $actualType)` raised when the relation exists but is neither a `BelongsTo` nor a `HasOne` (for example `HasMany`, `BelongsToMany`, `MorphTo`).

### `InvalidSortDirectionException`

Extends `\InvalidArgumentException`. One named constructor:

- `InvalidSortDirectionException::fromDirection(string $direction)` raised when the direction supplied to `orderByFullName` is not `'asc'` or `'desc'` (case insensitive).

Both exceptions are part of the public API. Users may catch either class directly or catch the broader `\InvalidArgumentException`.
