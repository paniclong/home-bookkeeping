<?php

declare(strict_types=1);

namespace App\Services;

use App\Helper\CurrencyHelper;
use App\Models\BotModel;
use App\ValueObject\Message;
use TelegramBot\Api\Types\ReplyKeyboardHide;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class MessageTransformer
{
    /**
     * @param string $text
     * @param BotModel $botNextStepModel
     *
     * @return Message
     */
    public function transform(string $text, BotModel $botNextStepModel): Message
    {
        switch ($botNextStepModel->getUserStep()) {
            case 'set_currency':
                $replyKeyboardMarkup = new ReplyKeyboardMarkup(
                    [
                        [
                            [
                                'text' => CurrencyHelper::RUB_CURRENCY,
                            ],
                            [
                                'text' => CurrencyHelper::USD_CURRENCY,
                            ],
                            [
                                'text' => CurrencyHelper::EUR_CURRENCY,
                            ],
                        ],
                    ],
                    null,
                    true
                );
                break;
            case 'receive_expenses':
            case 'receive_incomes':
                $replyKeyboardMarkup = new ReplyKeyboardMarkup(
                    [
                        [
                            [
                                'text' => '1 день',
                            ],
                            [
                                'text' => '1 неделя',
                            ],
                            [
                                'text' => '1 месяц',
                            ],
                            [
                                'text' => '1 год',
                            ],
                            [
                                'text' => 'За всё время',
                            ],
                        ],
                    ],
                    null,
                    true
                );
                break;
            default:
                $replyKeyboardMarkup = new ReplyKeyboardHide();

        }

        return new Message($text, null, false, null, $replyKeyboardMarkup);
    }
}
