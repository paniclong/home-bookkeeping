<?php

declare(strict_types=1);

namespace App\Services;

use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class TelegramBotHelper
{
    public const SEND_EXPENSES_COMMAND = 'send_expenses';
    public const SEND_INCOMES_COMMAND = 'send_incomes';
    public const RECEIVE_EXPENSES_COMMAND = 'receive_expenses';
    public const RECEIVE_INCOMES_COMMAND = 'receive_incomes';

    protected const BOT_COMMANDS = [
        self::SEND_EXPENSES_COMMAND,
        self::SEND_INCOMES_COMMAND,
    ];

    /**
     * @param Message $message
     *
     * @return string
     */
    public static function getCommand(Message $message): string
    {
        preg_match(Client::REGEXP, $message->getText(), $matches);

        if (empty($matches) || !in_array($matches[1], self::BOT_COMMANDS, true)) {
            return '';
        }

        return $matches[1];
    }

    /**
     * @param Message $message
     *
     * @return bool
     */
    public static function isCommand(Message $message): bool
    {
        preg_match(Client::REGEXP, $message->getText(), $matches);

        return !empty($matches);
    }
}
