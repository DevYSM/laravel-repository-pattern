# Laravel Repository Pattern

A lightweight Laravel package implementing the repository pattern for clean database operations, supporting Eloquent
relationships, sorting, and optional soft deletes. Compatible with Laravel 5.x to 11.x.

## Installation

1. Install via Composer:

   ```bash
   composer require ysm/laravel-repository-pattern
   ```

2. Publish configuration:

   ```bash
   php artisan vendor:publish --tag=config
   ```

## Configuration

Edit `config/repository-pattern.php` to define model-to-repository bindings:

```php
return [
    'bindings' => [
        \App\Models\User::class => \App\Repositories\UserRepository::class,
    ],
];
```

**Note**: The `RepositoryServiceProvider` binding logic overwrites multiple bindings. Use contextual bindings as a
workaround (see [Notes](#notes)).

## Artisan Commands

- **Generate a repository**:

  ```bash
  php artisan make:repository ModelName
  php artisan make:repository ModelName --soft-deletes
  ```

- **Generate a service**:

  ```bash
  php artisan make:service ModelName
  ```

## Methods

### RepositoryInterface / BaseRepository

- `getAll(array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): Collection`
    - Get all records with optional relationships and sorting.
- `paginate(int $perPage = 15, array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): LengthAwarePaginator`
    - Get paginated records with optional relationships and sorting.
- `show($value, string $column = 'id'): ?Model`
    - Get a record by value and column.
- `create(array $data): Model`
    - Create a new record.
- `update($id, array $data): Model`
    - Update a record by ID.
- `delete($id): bool`
    - Delete a record by ID (hard delete).
- `with(array $relations): self`
    - Load relationships for the query.
- `attach($id, string $relation, array $relatedIds): void`
    - Attach IDs to a many-to-many relationship.
- `detach($id, string $relation, array $relatedIds): void`
    - Detach IDs from a many-to-many relationship.
- `sync($id, string $relation, array $relatedIds): void`
    - Sync IDs for a many-to-many relationship.
- `whereHas(string $relation, Closure $callback): self`
    - Query records based on a relationship.

### BaseService

- Mirrors all `RepositoryInterface` methods, delegating to the repository.
- `getRepository(): RepositoryInterface`
    - Get the underlying repository.

### InteractsRepoWithSoftDeletes (Repository Trait)

- `permanentlyDelete($id): bool`
    - Permanently delete a soft-deleted record.
- `restore($id): bool`
    - Restore a soft-deleted record.

### InteractsServiceWithSoftDeletes (Service Trait)

- `permanentlyDelete($id): bool`
    - Permanently delete a soft-deleted record via repository.
- `restore($id): bool`
    - Restore a soft-deleted record via repository.
 
## License

MIT
