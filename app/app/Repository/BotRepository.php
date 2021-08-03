<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\BotCollection;
use App\Models\BotModel;
use Illuminate\Database\Eloquent\Collection;

class BotRepository extends AbstractRepository
{
    protected string $modelName = BotModel::class;

    /**
     * @param string $command
     *
     * @return Collection|BotCollection
     */
    public function findByCommand(string $command): BotCollection
    {
        return $this->findBy(['command' => $command]);
    }
}
