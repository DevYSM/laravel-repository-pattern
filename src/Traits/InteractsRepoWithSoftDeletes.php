<?php

namespace YSM\RepositoryPattern\Traits;

trait InteractsRepoWithSoftDeletes
{

    /**
     * @param $id
     *
     * @return bool
     */
    public function permanentlyDelete($id): bool
    {
        $this->validateSoftDeleteTrait();
        return $this->builder()->findOrFail($id)->forceDelete();
    }

    /**
     * @return void
     */
    private function validateSoftDeleteTrait()
    {
        if (!in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($this->model)))
            throw new \RuntimeException('Model does not use SoftDeletes trait.');
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function restore($id): bool
    {
        $this->validateSoftDeleteTrait();
        return $this->builder()->withTrashed()->findOrFail($id)->restore();
    }

}
