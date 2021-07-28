<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\ExpensesModel;
use Illuminate\Database\Eloquent\Model;

class ExpensesRepository extends AbstractRepository
{
    protected string $modelName = ExpensesModel::class;

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
