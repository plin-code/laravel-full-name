# Naming conventions

The package exposes four public macros across two layers. The two layers follow different naming conventions on purpose. This document explains why and shows how to recognize the pattern when reading or writing code that uses the package.

## The two patterns at a glance

The Eloquent Builder layer uses verb first names.

- `searchFullName(string $search, ...)`
- `orderByFullName(string $direction = 'asc', ...)`

The Filament TextColumn layer uses noun first names ending in `-able`.

- `fullNameSearchable(...)`
- `fullNameSortable(...)`

## Why Eloquent uses verb first

Eloquent query scopes and builder methods are imperative by convention. The operation name leads, and the object or attribute follows. A call like `->searchFullName($q)` reads as a verb phrase: "search by full name for the query `$q`". This aligns with the rest of the Eloquent surface:

- `where`, `orderBy`, `groupBy`, `having`, `with`, `load`, `paginate`
- User defined scopes typically follow the same pattern (`scopePublished`, `scopeOwnedBy`, `scopeSearchable`).

Putting the verb first keeps chains readable as a sequence of operations.

```php
Booking::query()
    ->searchFullName($q)
    ->orderByFullName('asc')
    ->whereDate('created_at', '>=', now()->subWeek())
    ->paginate();
```

## Why Filament uses noun first with `-able`

Filament table column configuration methods are mostly adjectives describing the column's capabilities. They read as predicates on the column.

- `searchable`, `sortable`, `toggleable`, `copyable`, `wrapping`, `extraAttributes`
- The `-able` suffix indicates a capability being enabled or configured, not an action being performed.

The package's Filament macros follow the same pattern. `->fullNameSearchable()` reads as "make this column searchable by full name" rather than "search by full name now".

```php
TextColumn::make('full_name')
    ->fullNameSearchable()
    ->fullNameSortable()
    ->wrap()
    ->copyable();
```

Using `->searchFullName()` here would feel out of place. It would look like an action to execute rather than a capability to configure.

## Why the two layers do not share names

Both framework conventions are strongly established and neither is going away. Renaming one layer to match the other would solve a minor internal inconsistency at the cost of a major conflict with the host framework's idioms. Users who write Eloquent code daily expect `searchFullName`. Users who write Filament code daily expect `fullNameSearchable`. The package respects both expectations.

The alternative, a single unified name (for example `fullName`), would be ambiguous: is it a search, a sort, a mutator, or something else? Separating the concern (search vs sort) into the method name is not optional.

## Recognizing the pattern when reading code

When you see a call chain, the layer is usually obvious from the chain itself.

- A chain starting with a model or query builder (`Booking::query()`, `$query->`, `User::whereIn(...)`) uses the verb first Eloquent macros.
- A chain starting with a Filament column (`TextColumn::make(...)`, `$column->`) uses the noun first Filament macros.

You never call both patterns on the same object. The layer is a function of the receiver, and the receiver makes the layer unambiguous.
