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

**Note**: The `RepositoryServiceProvider` doing auto binding for the model and repository.

## Artisan Commands

- **Generate a repository**:

  ```bash
  php artisan make:repository ModelName
  php artisan make:repository ModelName --soft-deletes
  ```

- **Generate a service**:

  ```bash
  php artisan make:service ModelName
  php artisan make:service ModelName --soft-deletes
  ```

## Methods

### RepositoryInterface / BaseRepository

- `getAll(array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): Collection`

- `paginate(int $perPage = 15, array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): LengthAwarePaginator`

- `show($value, string $column = 'id'): ?Model`

- `create(array $data): Model`

- `update($id, array $data): Model`

- `delete($id): bool`

- `with(array $relations): self`

- `attach($id, string $relation, array $relatedIds): void`

- `detach($id, string $relation, array $relatedIds): void`

- `sync($id, string $relation, array $relatedIds): void`

- `whereHas(string $relation, Closure $callback): self`
-
- `builder(): instance of model query builder`

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
