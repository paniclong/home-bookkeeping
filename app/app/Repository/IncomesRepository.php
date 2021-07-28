<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\IncomesModel;
use Illuminate\Database\Eloquent\Model;

class IncomesRepository extends AbstractRepository
{
    protected string $modelName = IncomesModel::class;

    /**
     * @param int $chatId
     *
     * @return Model|null
     */
    public function findOneByChatId(int $chatId): ?Model
    {
        return $this->findOneBy(['chat_id' => $chatId]);
    }
}
