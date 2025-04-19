<?php

namespace YSM\RepositoryPattern\Repositories;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use YSM\RepositoryPattern\Contracts\RepositoryInterface;

class BaseRepository implements RepositoryInterface
{

    /**
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    protected ?Model $model;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected \Illuminate\Database\Eloquent\Builder $query;

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->query = $this->builder();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function builder(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @param array  $relations
     * @param string $orderBy
     * @param string $orderDir
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): Collection
    {
        $query = $this->query->with($relations);
        $result = $query->orderBy($orderBy, $orderDir)->get();
        $this->resetQuery();
        return $result;
    }

    /**
     * @param array $relations
     *
     * @return $this
     */
    public function with(array $relations): self
    {
        $this->query = $this->query->with($relations);
        return $this;
    }

    /**
     * @return void
     */
    protected function resetQuery(): void
    {
        $this->query = $this->builder();
    }

    /**
     * @param int    $perPage
     * @param array  $relations
     * @param string $orderBy
     * @param string $orderDir
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): LengthAwarePaginator
    {
        $query = $this->query->orderBy($orderBy, $orderDir)->with($relations);
        $result = $query->paginate($perPage);
        $this->resetQuery();
        return $result;
    }

    /**
     * @param        $value
     * @param string $column
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show($value, string $column = 'id'): ?Model
    {
        return $this->query->where($column, $value)->firstOrFail();
    }

    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data): Model
    {
        return $this->builder()->create($data);
    }

    /**
     * @param       $id
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data): Model
    {
        $model = $this->builder()->findOrFail($id);
        $model->update($data);
        return $model;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function delete($id): bool
    {
        return $this->builder()->findOrFail($id)->delete();
    }

    /**
     * @param        $id
     * @param string $relation
     * @param array  $relatedIds
     *
     * @return void
     */
    public function attach($id, string $relation, array $relatedIds): void
    {
        $model = $this->builder()->findOrFail($id);
        $model->$relation()->attach($relatedIds);
    }

    /**
     * @param        $id
     * @param string $relation
     * @param array  $relatedIds
     *
     * @return void
     */
    public function detach($id, string $relation, array $relatedIds): void
    {
        $model = $this->builder()->findOrFail($id);
        $model->$relation()->detach($relatedIds);
    }

    /**
     * @param        $id
     * @param string $relation
     * @param array  $relatedIds
     *
     * @return void
     */
    public function sync($id, string $relation, array $relatedIds): void
    {
        $model = $this->builder()->findOrFail($id);
        $model->$relation()->sync($relatedIds);
    }

    /**
     * @param string   $relation
     * @param \Closure $callback
     *
     * @return $this
     */
    public function whereHas(string $relation, Closure $callback): self
    {
        $this->query = $this->query->whereHas($relation, $callback);
        return $this;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->resetQuery();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }
}
