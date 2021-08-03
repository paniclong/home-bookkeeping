<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\MessageModel;
use Illuminate\Database\Eloquent\Model;

class MessageRepository extends AbstractRepository
{
    protected string $modelName = MessageModel::class;

    /**
     * @param int $chatId
     *
     * @return MessageModel|Model|null
     */
    public function findOneByChatId(int $chatId): ?MessageModel
    {
        return $this->findOneBy(['chat_id' => $chatId]);
    }
}
