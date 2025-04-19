<?php

namespace YSM\RepositoryPattern\Services;

use Closure;
use YSM\RepositoryPattern\Contracts\RepositoryInterface;

class BaseService
{
    /**
     * @var \YSM\RepositoryPattern\Contracts\RepositoryInterface
     */
    protected RepositoryInterface $repository;

    /**
     * @param \YSM\RepositoryPattern\Contracts\RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array  $relations
     * @param string $orderBy
     * @param string $orderDir
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getAll($relations, $orderBy, $orderDir);
    }

    /**
     * @param int    $perPage
     * @param array  $relations
     * @param string $orderBy
     * @param string $orderDir
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $relations = [], string $orderBy = 'created_at', string $orderDir = 'DESC'): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $relations, $orderBy, $orderDir);
    }

    /**
     * @param        $value
     * @param string $column
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show($value, string $column = 'id'): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->repository->show($value, $column);
    }

    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->repository->create($data);
    }

    /**
     * @param       $id
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->repository->update($id, $data);
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function delete($id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * @param array $relations
     *
     * @return \YSM\RepositoryPattern\Contracts\RepositoryInterface
     */
    public function with(array $relations): RepositoryInterface
    {
        return $this->repository->with($relations);
    }

    /**
     * @param        $id
     * @param string $relation
     * @param array  $relatedIds
     *
     * @return null
     */
    public function attach($id, string $relation, array $relatedIds)
    {
        return $this->repository->attach($id, $relation, $relatedIds);
    }

    /**
     * @param        $id
     * @param string $relation
     * @param array  $relatedIds
     *
     * @return null
     */
    public function detach($id, string $relation, array $relatedIds)
    {
        return $this->repository->detach($id, $relation, $relatedIds);
    }

    /**
     * @param        $id
     * @param string $relation
     * @param array  $relatedIds
     *
     * @return null
     */
    public function sync($id, string $relation, array $relatedIds)
    {
        return $this->repository->sync($id, $relation, $relatedIds);
    }

    /**
     * @param string   $relation
     * @param \Closure $callback
     *
     * @return \YSM\RepositoryPattern\Contracts\RepositoryInterface
     */
    public function whereHas(string $relation, Closure $callback): RepositoryInterface
    {
        return $this->repository->whereHas($relation, $callback);
    }

    /**
     * @return \YSM\RepositoryPattern\Contracts\RepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
