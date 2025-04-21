# Laravel Repository Pattern

A lightweight Laravel package implementing the Repository Design Pattern with a Service Layer for clean database
operations, supporting Eloquent relationships, sorting, and optional soft deletes. Compatible with Laravel 8.0 to 12.0
and PHP 8.0+.

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

Edit `config/repository-pattern.php` to manually define model-to-repository bindings:

```php
return [
    'bindings' => [
        \App\Models\User::class => \App\Repositories\UserRepository::class,
    ],
];
```

## Artisan Commands

- **Generate a repository**:

  ```bash
  php artisan make:repository ModelName [--soft-deletes]
  ```

- **Generate a service**:

  ```bash
  php artisan make:service ModelName [--soft-deletes]
  ```

- **Generate a controller**:

  ```bash
  php artisan ysm:controller ModelName [--type=api|web] [--dir=CustomDir] [--soft-deletes] [--with-routes] [--with-rs]
  --type=api|web: Specify controller type (default: api)
  --dir=CustomDir: Custom controller and route directory (e.g., Api\Admin\v1)
  --soft-deletes: Include soft delete methods
  --with-routes: Generate and bind route file
  --with-rs: Generate repository and service
  ```

## Methods

### RepositoryInterface / BaseRepository

- `getAll(array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC')`
    - Get all records with optional relationships and sorting.
- `paginate(int $perPage = 15, array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC')`
    - Get paginated records with optional relationships and sorting.
- `show($value, string $column = 'id')`
    - Get a record by value and column.
- `create(array $data)`
    - Create a new record.
- `update($id, array $data)`
    - Update a record by ID.
- `delete($id)`
    - Delete a record by ID (hard delete).
- `with(array $relations)`
    - Load relationships for the query.
- `attach($id, string $relation, array $relatedIds)`
    - Attach IDs to a many-to-many relationship.
- `detach($id, string $relation, array $relatedIds)`
    - Detach IDs from a many-to-many relationship.
- `sync($id, string $relation, array $relatedIds)`
    - Sync IDs for a many-to-many relationship.
- `whereHas(string $relation, Closure $callback)`
    - Query records based on a relationship.

### BaseService

- Mirrors all `RepositoryInterface` methods, delegating to the repository.
- `getRepository()`
    - Get the underlying repository.

### InteractsRepoWithSoftDeletes (Repository Trait)

- `softDelete($id)`
    - Soft delete a record by ID.
- `permanentlyDelete($id)`
    - Permanently delete a soft-deleted record.
- `restore($id)`
    - Restore a soft-deleted record.

### InteractsServiceWithSoftDeletes (Service Trait)

- `softDelete($id)`
    - Soft delete a record via repository.
- `permanentlyDelete($id)`
    - Permanently delete a soft-deleted record via repository.
- `restore($id)`
    - Restore a soft-deleted record via repository.

## Notes

- **Compatibility**: Requires Laravel 8.0–12.0 and PHP 8.0+. Ensure your project meets these requirements.
- **Bindings**: Model-to-repository bindings must be manually added to `config/repository-pattern.php`. No default
  binding exists for unbound models; consider adding a default repository or exception.
- **Controller Directory**: Use `--dir=CustomDir` to place controllers in `App\Http\Controllers\CustomDir` (
  e.g., `Api\Admin\v1`) and routes in `routes/CustomDir/`.
- **Route Generation**: The `--with-routes` flag for `ysm:controller` generates a route file
  in `routes/api/`, `routes/web/`, or `routes/CustomDir/` (e.g., `routes/Api/Admin/v1/users.php`) and binds it
  in `RouteServiceProvider`. Outputs “🎉 Routes created successfully!”.
- **Repository and Service Generation**: The `--with-rs` flag for `ysm:controller` generates corresponding
  repository and service files, respecting `--soft-deletes`.
- **Controller Overwrite**: The `ysm:controller` command overwrites existing controllers, displaying “🎉
  Controller [Name] (type) overwritten successfully.” or “🎉 Controller [Name] (type) created successfully.”
- **Repository Overwrite**: The `make:repository` command overwrites existing repositories, displaying
  “Repository [Name] overwritten successfully.” or “Repository [Name] created successfully.”

## License

MIT
