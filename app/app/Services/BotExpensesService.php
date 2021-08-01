<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExpensesModel;
use App\Models\MessagesModel;
use App\Repository\ExpensesRepository;
use Illuminate\Database\Eloquent\Collection;
use TelegramBot\Api\Types\Message;

class BotExpensesService
{
    protected ExpensesRepository $expensesRepository;

    /**
     * @param ExpensesRepository $expensesRepository
     */
    public function __construct(ExpensesRepository $expensesRepository)
    {
        $this->expensesRepository = $expensesRepository;
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

        $expensesModel = $this->expensesRepository->findOneByChatId($chatId);

        if ($expensesModel === null) {
            $expensesModel = new ExpensesModel();
            $expensesModel->setAttribute('amount', (double)$text);
            $expensesModel->setAttribute('chat_id', $chatId);
        } else if ($step === 2) {
            $expensesModel->setAttribute('count', (int)$text);
        } elseif ($step === 3) {
            $expensesModel->setAttribute('description', $text);
            $expensesModel->setAttribute('chat_id', null);
        }

        $messageModel->setAttribute('step', $step + 1);
        $expensesModel->save();
    }

    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->expensesRepository->findAll();
    }
}
