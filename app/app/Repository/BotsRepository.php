<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\BotCollection;
use App\Models\BotsModel;
use Illuminate\Database\Eloquent\Collection;

class BotsRepository extends AbstractRepository
{
    protected string $modelName = BotsModel::class;

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
