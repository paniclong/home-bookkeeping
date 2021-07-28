<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\BotsModel;
use Illuminate\Database\Eloquent\Collection;

class BotsRepository extends AbstractRepository
{
    protected string $modelName = BotsModel::class;

    /**
     * @param string $command
     *
     * @return Collection
     */
    public function findByCommand(string $command): Collection
    {
        return $this->findBy(['command' => $command]);
    }
}
