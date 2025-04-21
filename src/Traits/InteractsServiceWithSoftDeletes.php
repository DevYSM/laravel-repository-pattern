<?php

namespace YSM\RepositoryPattern\Traits;

trait
InteractsServiceWithSoftDeletes
{
    /**
     * @param $id
     *
     * @return bool
     */
    public function permanentlyDelete($id): bool
    {
        $this->validateSoftDeleteTrait();
        return $this->repository->permanentlyDelete($id);
    }

    /**
     * @return void
     */
    private function validateSoftDeleteTrait()
    {
        if (!in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($this->repository->getModel())))
            throw new \RuntimeException('Repository or Model does not use SoftDeletes trait.');
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function restore($id): bool
    {
        $this->validateSoftDeleteTrait();
        return $this->repository->restore($id);
    }


}
