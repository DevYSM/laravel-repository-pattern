<?php

namespace YSM\RepositoryPattern\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * @param array  $relations
     * @param string $orderBy
     * @param string $orderDir
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): Collection;

    /**
     * @param int    $perPage
     * @param array  $relations
     * @param string $orderBy
     * @param string $orderDir
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): LengthAwarePaginator;

    /**
     * @param        $value
     * @param string $column
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show($value, string $column = 'id'): ?Model;

    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data): Model;

    /**
     * @param       $id
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data): Model;

    /**
     * @param $id
     *
     * @return bool
     */
    public function delete($id): bool;

    /**
     * @param array $relations
     *
     * @return $this
     */
    public function with(array $relations): self;

    /**
     * @param        $id
     * @param string $relation
     * @param array  $relatedIds
     *
     * @return void
     */
    public function attach($id, string $relation, array $relatedIds): void;

    /**
     * @param        $id
     * @param string $relation
     * @param array  $relatedIds
     *
     * @return void
     */
    public function detach($id, string $relation, array $relatedIds): void;

    /**
     * @param        $id
     * @param string $relation
     * @param array  $relatedIds
     *
     * @return void
     */
    public function sync($id, string $relation, array $relatedIds): void;

    /**
     * @param string   $relation
     * @param \Closure $callback
     *
     * @return $this
     */
    public function whereHas(string $relation, Closure $callback): self;
}
