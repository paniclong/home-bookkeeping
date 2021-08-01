<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Factory\MessageFactory;
use App\Models\BotsModel;
use App\Models\ExpensesModel;
use App\Models\IncomesModel;
use App\Models\MessagesModel;
use App\Repository\BotsRepository;
use App\Repository\MessagesRepository;
use App\Services\BotExpensesService;
use App\Services\BotIncomesService;
use App\Services\TelegramBotHelper;
use Closure;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use Throwable;

class BotController extends Controller
{
    use ValidatesRequests;

    protected LoggerInterface $logger;
    protected MessagesRepository $messagesRepository;
    protected BotsRepository $botsRepository;
    protected BotExpensesService $botExpensesService;
    protected BotIncomesService $botIncomesService;
    protected Client $botClient;
    protected Client $errorBotClient;

    /**
     * @param Client $botClient
     * @param Client $errorBotClient
     * @param LoggerInterface $logger
     * @param MessagesRepository $messagesRepository
     * @param BotsRepository $botsRepository
     * @param BotExpensesService $botExpensesService
     * @param BotIncomesService $botIncomesService
     */
    public function __construct(
        Client $botClient,
        Client $errorBotClient,
        LoggerInterface $logger,
        MessagesRepository $messagesRepository,
        BotsRepository $botsRepository,
        BotExpensesService $botExpensesService,
        BotIncomesService $botIncomesService
    ) {
        $this->logger = $logger;
        $this->messagesRepository = $messagesRepository;
        $this->botsRepository = $botsRepository;
        $this->botExpensesService = $botExpensesService;
        $this->botIncomesService = $botIncomesService;
        $this->botClient = $botClient;
        $this->errorBotClient = $errorBotClient;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        try {
            $this->logger->info('Get webhook from telegram');

            $checker = static function () {
                return true;
            };

            /**
             * Каждую команду надо разделить в отдельный сервис для обработки
             */
            //Handle `send_expenses` and `send_incoming` first run command from user
            $this->botClient->on($this->getFirstCommandCallback(), $checker);
            // Handle `receive_expenses` command
            $this->botClient->command(TelegramBotHelper::RECEIVE_EXPENSES_COMMAND, $this->getReceiveExpensesCommandCallback());
            // Handle `receive_incomes` command
            $this->botClient->command(TelegramBotHelper::RECEIVE_INCOMES_COMMAND, $this->getReceiveIncomesCommandCallback());
            // Handle regular messages from user
            $this->botClient->on($this->getTextMessagesCallback(), $checker);

            $this->botClient->run();
        } catch (Throwable $ex) {
            $this->logger->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);

            $this->errorBotClient->sendMessage(
                config('telegram.error_chat_id'),
                sprintf('Error! Method - %s, Message - %s', __METHOD__, $ex->getMessage())
            );
        }

        return new JsonResponse();
    }

    /**
     * @return Closure
     */
    protected function getFirstCommandCallback(): Closure
    {
        return function (Update $update) {
            $message = $update->getMessage();

            if ($message === null) {
                $this->logger->info('Message is empty, skipping');
                return true;
            }

            if (!$command = TelegramBotHelper::getCommand($message)) {
                $this->logger->info('Message is not command, skipping');
                return true;
            }

            $chatId = $message->getChat()->getId();
            $messageModel = $this->messagesRepository->findOneByChatId($chatId);

            if ($messageModel === null) {
                $botCollection = $this->botsRepository->findByCommand($command);

                /** @var BotsModel $firstBotStep */
                $firstBotStep = $botCollection->first();

                $messageModel = MessageFactory::create(
                    $chatId,
                    $message->getMessageId(),
                    $firstBotStep->getStepId(),
                    $command
                );

                $messageModel->save();

                $this->botClient->sendMessage($chatId, $firstBotStep->getBotStep());

                $this->logger->info(sprintf('Success handle command - %s', $message->getText()));

                return false;
            }

            $this->logger->info('Nothing to send first message');

            return true;
        };
    }

    /**
     * @return Closure
     */
    protected function getTextMessagesCallback(): Closure
    {
        return function (Update $update) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();

            if (TelegramBotHelper::isCommand($message)) {
                $this->botClient->sendMessage($chatId, 'Введите верное значение');
                $this->logger->info('Message is command, skipping');

                return true;
            }

            /** @var MessagesModel $messageModel */
            $messageModel = $this->messagesRepository->findOneByChatId($chatId);

            if ($messageModel === null) {
                $this->botClient->sendMessage($chatId, 'Неизвестная команда');
                $this->logger->info('Last message is empty, skipping');

                return true;
            }

            switch ($messageModel->getCommand()) {
                case TelegramBotHelper::SEND_EXPENSES_COMMAND: $this->botExpensesService->handle($message, $messageModel);
                    break;
                case TelegramBotHelper::SEND_INCOMES_COMMAND: $this->botIncomesService->handle($message, $messageModel);
                    break;
            }

            $botCollection = $this->botsRepository->findByCommand($messageModel->getCommand());
            $botStepModel = $botCollection->findByStepId($messageModel->getStep());

            $this->botClient->sendMessage($chatId, $botStepModel->getBotStep());

            $lastModel = $botCollection->last();

            $messageModel->isLastStep($lastModel->getStepId()) ? $messageModel->delete() : $messageModel->save();

            $this->logger->info(sprintf('Success handle text message - %s', $message->getText()));

            return true;
        };
    }

    /**
     * @return Closure
     */
    protected function getReceiveExpensesCommandCallback(): Closure
    {
        return function (Message $message) {
            $expensesCollection = $this->botExpensesService->getAll();

            $string = '';

            $expensesCollection->map(function (ExpensesModel $model) use (&$string) {
                $string .= 'Покупка № ' . $model->getQueueableId() . PHP_EOL
                    . 'цена - ' . $model->getAttribute('amount') . PHP_EOL
                    . 'количество - ' . $model->getAttribute('count') . PHP_EOL
                    . 'описание - ' . $model->getAttribute('description')
                    . PHP_EOL . PHP_EOL;
            });

            $string .= PHP_EOL . PHP_EOL
                . 'Общие траты за весь период - ' . PHP_EOL
                . (number_format((float) $expensesCollection->sum('amount'), 0, '', ' '))
                . ' руб';

            $this->logger->info($string);

            $this->botClient->sendMessage($message->getChat()->getId(), $string);

            return false;
        };
    }

    /**
     * @return Closure
     */
    protected function getReceiveIncomesCommandCallback(): Closure
    {
        return function (Message $message) {
            $incomesCollection = $this->botIncomesService->getAll();

            $string = '';

            $incomesCollection->map(function (IncomesModel $model) use (&$string) {
                $string .= 'Прибыль № ' . $model->getQueueableId()
                    . ', сумма - ' . $model->getAttribute('amount')
                    . ', описание - ' . $model->getAttribute('description')
                    . PHP_EOL;
            });

            $string .= PHP_EOL . PHP_EOL
                . 'Общая сумма прибыли за весь период - ' . PHP_EOL
                . (number_format((float) $incomesCollection->sum('amount'), 0, '', ' '))
                . ' руб';

            $this->logger->info($string);

            $this->botClient->sendMessage($message->getChat()->getId(), $string);

            return false;
        };
    }
}
