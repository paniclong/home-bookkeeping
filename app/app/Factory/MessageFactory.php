<?php

declare(strict_types=1);

namespace App\Factory;

use App\Models\MessageModel;

class MessageFactory
{
    /**
     * @param int $chatId
     * @param int $messageId
     * @param int $step
     * @param string $command
     *
     * @return MessageModel
     */
    public static function create(int $chatId, int $messageId, int $step, string $command): MessageModel
    {
        $messageModel = new MessageModel();

        $messageModel
            ->setChatId($chatId)
            ->setMessageId($messageId)
            ->setStep($step)
            ->setCommand($command);

        return $messageModel;
    }
}
