<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IncomesModel;
use App\Models\MessagesModel;
use App\Repository\IncomesRepository;
use Illuminate\Database\Eloquent\Collection;
use TelegramBot\Api\Types\Message;

class BotIncomesService
{
    protected IncomesRepository $incomesRepository;

    /**
     * @param IncomesRepository $incomesRepository
     */
    public function __construct(IncomesRepository $incomesRepository)
    {
        $this->incomesRepository = $incomesRepository;
    }

    /**
     * @param Message $telegramMessage
     * @param MessagesModel $messageModel
     */
    public function handle(Message $telegramMessage, MessagesModel $messageModel): void
    {
        $chatId = $telegramMessage->getChat()->getId();
        $text = $telegramMessage->getText();
        $step = $messageModel->getStep();

        $incomesModel = $this->incomesRepository->findOneByChatId($chatId);

        if ($incomesModel === null) {
            $incomesModel = new IncomesModel();
            $incomesModel->setAttribute('amount', (double)$text);
            $incomesModel->setAttribute('chat_id', $chatId);
        } else if ($step === 2) {
            $incomesModel->setAttribute('description', $text);
            $incomesModel->setAttribute('chat_id', null);
        }

        $messageModel->setAttribute('step', $step + 1);
        $incomesModel->save();
    }

    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->incomesRepository->findAll();
    }
}
