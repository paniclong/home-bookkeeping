<?php

declare(strict_types=1);

namespace App\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    protected string $modelName;

    /**
     * @param array $criteria
     *
     * @param int $limit
     *
     * @return Collection
     */
    public function findBy(array $criteria, int $limit = 1000): Collection
    {
        return $this->getModel()
            ->where($criteria)
            ->limit($limit)
            ->get();
    }

    /**
     * @param array $criteria
     *
     * @return Model|null
     */
    public function findOneBy(array $criteria): ?Model
    {
        return $this->getModel()
            ->where($criteria)
            ->limit(1)
            ->first();
    }

    /**
     * @param int $id
     *
     * @return Model|null
     */
    public function findOneById(int $id): ?Model
    {
        return $this->getModel()
            ->where(['id' => $id])
            ->limit(1)
            ->first();
    }

    /**
     * @return Collection
     */
    public function findAll(): Collection
    {
        return $this->getModel()->get();
    }

    /**
     * @return Builder
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function getModel(): Builder
    {
        return $this->modelName::query();
    }
}
