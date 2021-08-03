<?php

declare(strict_types=1);

namespace App\Services;

use App\Collection\BotCollection;
use App\Models\ExpensesModel;
use App\Models\IncomesModel;
use App\Models\MessageModel;
use TelegramBot\Api\Types\Message;

class BotStepService
{
    protected MessageTransformer $messageTransformer;

    /**
     * @param MessageTransformer $messageTransformer
     */
    public function __construct(MessageTransformer $messageTransformer)
    {
        $this->messageTransformer = $messageTransformer;
    }

    /**
     * @param ExpensesModel|IncomesModel $model
     * @param Message $telegramMessage
     * @param MessageModel $messageModel
     * @param BotCollection $botCollection
     *
     * @return \App\ValueObject\Message
     */
    public function handle(
        $model,
        Message $telegramMessage,
        MessageModel $messageModel,
        BotCollection $botCollection
    ): \App\ValueObject\Message {
        $chatId = $telegramMessage->getChat()->getId();
        $text = $telegramMessage->getText();
        $step = $messageModel->getStep();

        $botModel = $botCollection->findOneByStepId($step);

        switch ($botModel->getUserStep()) {
            case 'set_amount':
                /**
                 * @todo validate
                 */
                $model->setAmount((double)$text);
                $model->setChatId($chatId);
                break;
            case 'set_count':
                /**
                 * @todo validate
                 */
                $model->setCount((int)$text);
                break;
            case 'set_currency':
                /**
                 * @todo validate
                 */
                $model->setCurrency($text);
                break;
            case 'set_description':
                /**
                 * @todo validate
                 */
                $model->setDescription($text);
                $model->setChatId(null);
                break;
        }

        if ($botCollection->isLastStep($botModel->getNextStepId())) {
            $messageModel->delete();
        } else {
            $messageModel->setStep($botModel->getNextStepId());
            $messageModel->save();
        }

        $model->save();

        $nextStepModel = $botCollection->findOneByStepId($botModel->getNextStepId());

        return $this->messageTransformer->transform($nextStepModel);
    }
}
