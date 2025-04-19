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

- `getAll($relations = [], $orderBy = 'created_at', $orderDir = 'DESC')`
    - Get all records with optional relations and sorting.
- `paginate($perPage = 15, $relations = [], $orderBy = 'created_at',$orderDir = 'DESC')`
    - Paginate records with optional relations and sorting.
- `show($value, $column = 'id')`
    - Get a single record by ID or other column.
- `create(array $data)`
    - Create a new record.
- `update($id, $data)`
    - Update an existing record by ID.
- `delete($id)`
    - Soft delete a record by ID.
- `with( $relations)`
    - Specify relations to eager load.
- `attach($id,$relation, $relatedIds)`
    - Attach related records to a model.
- `detach($id,$relation,$relatedIds)`
    - Detach related records from a model.
- `sync($id,$relation, $relatedIds)`
    - Sync related records with a model.
- `whereHas($relation, Closure $callback)`
    - Filter records based on a related model's attributes.
- `builder()`
    - Get the underlying Eloquent query builder.

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
