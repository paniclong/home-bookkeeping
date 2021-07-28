<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\MessagesModel;
use Illuminate\Database\Eloquent\Model;

class MessagesRepository extends AbstractRepository
{
    protected string $modelName = MessagesModel::class;

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
