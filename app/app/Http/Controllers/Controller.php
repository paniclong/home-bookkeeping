<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Factory\MessageFactory;
use App\Models\BotsModel;
use App\Models\MessagesModel;
use App\Repository\BotsRepository;
use App\Repository\MessagesRepository;
use App\Services\BotExpensesService;
use App\Services\BotIncomesService;
use App\Services\TelegramBotHelper;
use Closure;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected LoggerInterface $logger;
    protected MessagesRepository $messagesRepository;
    protected BotsRepository $botsRepository;
    protected BotExpensesService $botExpensesService;
    protected BotIncomesService $botIncomesService;

    /**
     * @param LoggerInterface $logger
     * @param MessagesRepository $messagesRepository
     * @param BotsRepository $botsRepository
     * @param BotExpensesService $botExpensesService
     * @param BotIncomesService $botIncomesService
     */
    public function __construct(
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
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @return JsonResponse
     */
    public function webhook(): JsonResponse
    {
        $this->logger->info('Get webhook from telegram');

        try {
            $bot = new Client('1877197075:AAGqDO0bGa7iRG6FHjHv7yVd9l6PK7c0vvc');

            $checker = static function () {
                return true;
            };

            //Handle `send_expenses` and `send_incoming` first run command from user
            $bot->on($this->getFirstCommandCallback($bot), $checker);
            // Handle regular messages from user
            $bot->on($this->getTextMessagesCallback($bot), $checker);

            $bot->run();

        } catch (Throwable $ex) {
            $this->logger->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);

            $errorBot = new Client('1926267774:AAEGHw_6Gcj_y4dTTrLImXgDFaCQVlCiFC4');
            $errorBot->sendMessage(451296313, sprintf('Error! Method - %s, Message - %s', __METHOD__, $ex->getMessage()));
        }

        return new JsonResponse();
    }

    /**
     * @param Client $bot
     *
     * @return Closure
     */
    protected function getFirstCommandCallback(Client $bot): Closure
    {
        return function (Update $update) use ($bot) {
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
                $botModel = $this->botsRepository->findByCommand($command);

                /** @var BotsModel $firstStep */
                $firstStep = $botModel->first();

                $messageModel = MessageFactory::create($chatId, $message->getMessageId(), $firstStep->getStep(), $command);
                $messageModel->save();

                $bot->sendMessage($message->getChat()->getId(), $firstStep->getBotStep());

                return false;
            }

            $this->logger->info('Nothing to send first message');

            return true;
        };
    }

    /**
     * @param Client $bot
     *
     * @return Closure
     */
    protected function getTextMessagesCallback(Client $bot): Closure
    {
        return function (Update $update) use ($bot) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();

            if (TelegramBotHelper::isCommand($message)) {
                $bot->sendMessage($chatId, 'Введите верное значение');
                $this->logger->info('Message is command, skipping');

                return true;
            }

            /** @var MessagesModel $messageModel */
            $messageModel = $this->messagesRepository->findOneByChatId($chatId);

            if ($messageModel === null) {
                $bot->sendMessage($chatId, 'Неизвестная команда');
                $this->logger->info('Last message is empty, skipping');

                return true;
            }

            switch ($messageModel->getCommand()) {
                case TelegramBotHelper::SEND_EXPENSES_COMMAND: $this->botExpensesService->handle($message, $messageModel);
                    break;
                case TelegramBotHelper::SEND_INCOMES_COMMAND: $this->botIncomesService->handle($message, $messageModel);
                    break;
            }

            $botModel = $this->botsRepository->findByCommand($messageModel->getCommand());
            $stepModel = $botModel->firstWhere('step', '=', $messageModel->getStep());
            $bot->sendMessage($chatId, $stepModel->getBotStep());

            $lastModel = $botModel->last();

            $messageModel->isLastStep($lastModel->getStep()) ? $messageModel->delete() : $messageModel->save();

            return true;
        };
    }
}
