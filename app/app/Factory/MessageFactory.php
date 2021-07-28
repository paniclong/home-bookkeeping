<?php

declare(strict_types=1);

namespace App\Factory;

use App\Models\MessagesModel;

class MessageFactory
{
    /**
     * @param int $chatId
     * @param int $messageId
     * @param int $step
     * @param string $command
     *
     * @return MessagesModel
     */
    public static function create(int $chatId, int $messageId, int $step, string $command): MessagesModel
    {
        $messageModel = new MessagesModel();

        $messageModel
            ->setChatId($chatId)
            ->setMessageId($messageId)
            ->setStep($step)
            ->setCommand($command);

        return $messageModel;
    }
}
