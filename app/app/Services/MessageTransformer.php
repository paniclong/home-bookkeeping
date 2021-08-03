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
     * @param BotModel $botNextStepModel
     *
     * @return Message
     */
    public function transform(BotModel $botNextStepModel): Message
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
            default:
                $replyKeyboardMarkup = new ReplyKeyboardHide();
        }

        return new Message(
            $botNextStepModel->getBotStep(),
            null,
            false,
            null,
            $replyKeyboardMarkup
        );
    }
}
