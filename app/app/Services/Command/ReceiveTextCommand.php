<?php

declare(strict_types=1);

namespace App\Services\Command;

use App\Helper\TelegramBotHelper;
use App\Models\ExpensesModel;
use App\Models\IncomesModel;
use App\Repository\BotRepository;
use App\Repository\ExpensesRepository;
use App\Repository\IncomesRepository;
use App\Repository\MessageRepository;
use App\Services\BotStepService;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\ReplyKeyboardHide;
use TelegramBot\Api\Types\Update;

class ReceiveTextCommand implements CommandInterface
{
    protected LoggerInterface $logger;
    protected MessageRepository $messageRepository;
    protected BotRepository $botRepository;
    protected BotStepService $botStepService;
    protected ExpensesRepository $expensesRepository;
    protected IncomesRepository $incomesRepository;

    /**
     * @param LoggerInterface $logger
     * @param MessageRepository $messageRepository
     * @param BotRepository $botRepository
     * @param BotStepService $botStepService
     * @param ExpensesRepository $expensesRepository
     * @param IncomesRepository $incomesRepository
     */
    public function __construct(
        LoggerInterface $logger,
        MessageRepository $messageRepository,
        BotRepository $botRepository,
        BotStepService $botStepService,
        ExpensesRepository $expensesRepository,
        IncomesRepository $incomesRepository
    ) {
        $this->logger = $logger;
        $this->messageRepository = $messageRepository;
        $this->botRepository = $botRepository;
        $this->botStepService = $botStepService;
        $this->expensesRepository = $expensesRepository;
        $this->incomesRepository = $incomesRepository;
    }

    /**
     * @param Client $client
     */
    public function register(Client $client): void
    {
        $checker = static function () {
            return true;
        };

        $client->on(function (Update $update) use ($client) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();

            if (TelegramBotHelper::isCommand($message)) {
                $client->sendMessage($chatId, 'Введите верное значение');
                $this->logger->info('Message is command, skipping');

                return true;
            }

            $messageModel = $this->messageRepository->findOneByChatId($chatId);

            if ($messageModel === null) {
                $client->sendMessage($chatId, 'Неизвестная команда');
                $this->logger->info('Last message is empty, skipping');

                return true;
            }

            $botCollection = $this->botRepository->findByCommand($messageModel->getCommand());

            switch ($messageModel->getCommand()) {
                case TelegramBotHelper::SEND_EXPENSES_COMMAND:
                    $model = $this->expensesRepository->findOneByChatId($chatId) ?: new ExpensesModel();
                    $messageObject = $this->botStepService->handle($model, $message, $messageModel, $botCollection);
                    break;
                case TelegramBotHelper::SEND_INCOMES_COMMAND:
                    $model = $this->incomesRepository->findOneByChatId($chatId) ?: new IncomesModel();
                    $messageObject = $this->botStepService->handle($model, $message, $messageModel, $botCollection);
                    break;
                case TelegramBotHelper::RECEIVE_EXPENSES_COMMAND:
                case TelegramBotHelper::RECEIVE_INCOMES_COMMAND:
                    $messageObject = $this->botStepService->handleNoEntityCommand($message, $messageModel, $botCollection);
                    break;
                default:
                    return true;
            }

            $client->sendMessage(
                $chatId,
                $messageObject->getText(),
                $messageObject->getParseMode(),
                $messageObject->isDisablePreview(),
                $messageObject->getReplyToMessageId(),
                $messageObject->getReplyMarkup(),
                $messageObject->isDisableNotification()
            );

            $this->logger->info(sprintf('Success handle text message - %s', $message->getText()));

            return true;
        }, $checker);
    }
}
