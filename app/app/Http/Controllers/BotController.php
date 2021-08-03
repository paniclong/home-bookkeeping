<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Factory\MessageFactory;
use App\Helper\CurrencyHelper;
use App\Helper\PriceHelper;
use App\Models\ExpensesModel;
use App\Models\IncomesModel;
use App\Repository\BotRepository;
use App\Repository\ExpensesRepository;
use App\Repository\IncomesRepository;
use App\Repository\MessageRepository;
use App\Services\BotStepService;
use App\Helper\TelegramBotHelper;
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
    protected MessageRepository $messageRepository;
    protected BotRepository $botRepository;
    protected BotStepService $botStepService;
    protected Client $botClient;
    protected Client $errorBotClient;
    protected ExpensesRepository $expensesRepository;
    protected IncomesRepository $incomesRepository;

    /**
     * @param Client $botClient
     * @param Client $errorBotClient
     * @param LoggerInterface $logger
     * @param MessageRepository $messageRepository
     * @param BotRepository $botRepository
     * @param BotStepService $botStepService
     * @param ExpensesRepository $expensesRepository
     * @param IncomesRepository $incomesRepository
     */
    public function __construct(
        Client             $botClient,
        Client             $errorBotClient,
        LoggerInterface    $logger,
        MessageRepository  $messageRepository,
        BotRepository      $botRepository,
        BotStepService     $botStepService,
        ExpensesRepository $expensesRepository,
        IncomesRepository  $incomesRepository
    ) {
        $this->logger = $logger;
        $this->messageRepository = $messageRepository;
        $this->botRepository = $botRepository;
        $this->botStepService = $botStepService;
        $this->botClient = $botClient;
        $this->errorBotClient = $errorBotClient;
        $this->expensesRepository = $expensesRepository;
        $this->incomesRepository = $incomesRepository;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        try {
            $this->logger->info('Get webhook from telegram', ['raw_body' => $this->botClient->getRawBody()]);

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
            $messageModel = $this->messageRepository->findOneByChatId($chatId);

            if ($messageModel === null) {
                $botModel = $this->botRepository->findByCommand($command)->getFirst();

                $messageModel = MessageFactory::create(
                    $chatId,
                    $message->getMessageId(),
                    $botModel->getStepId(),
                    $command
                );

                $messageModel->save();

                $this->botClient->sendMessage($chatId, $botModel->getBotStep());

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

            $messageModel = $this->messageRepository->findOneByChatId($chatId);

            if ($messageModel === null) {
                $this->botClient->sendMessage($chatId, 'Неизвестная команда');
                $this->logger->info('Last message is empty, skipping');

                return true;
            }

            $botCollection = $this->botRepository->findByCommand($messageModel->getCommand());

            switch ($messageModel->getCommand()) {
                case TelegramBotHelper::SEND_EXPENSES_COMMAND:
                    $model = $this->expensesRepository->findOneByChatId($chatId) ?: new ExpensesModel();
                    break;
                case TelegramBotHelper::SEND_INCOMES_COMMAND:
                    $model = $this->incomesRepository->findOneByChatId($chatId) ?: new IncomesModel();
                    break;
                default:
                    throw new \Exception('Unknown command');
            }

            $messageObject = $this->botStepService->handle($model, $message, $messageModel, $botCollection);

            $this->botClient->sendMessage(
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
        };
    }

    /**
     * @return Closure
     */
    protected function getReceiveExpensesCommandCallback(): Closure
    {
        return function (Message $message) {
            $expensesCollection = $this->expensesRepository->findAll();

            $textMessage = '';
            $allSum = 0;

            $expensesCollection->map(function (ExpensesModel $model) use (&$textMessage, &$allSum) {
                $currency = $model->getCurrency() ?: CurrencyHelper::RUB_CURRENCY;
                $price = $model->getAmount();

                switch ($currency) {
                    case CurrencyHelper::USD_CURRENCY: $price *= 72.43; break;
                    case CurrencyHelper::ALL_CURRENCY: $price *= 86.43; break;
                }

                $allSum += $price;

                $textMessage .= 'Покупка № ' . $model->getQueueableId() . PHP_EOL
                    . 'цена - ' . $price . PHP_EOL
                    . 'количество - ' . $model->getCount() . PHP_EOL
                    . 'описание - ' . $model->getDescription() . PHP_EOL
                    . 'дата - ' . $model->getCreatedAt() . PHP_EOL . PHP_EOL;
            });

            $textMessage .= 'Общие траты за весь период: ' . PHP_EOL . PriceHelper::formatSumToString($allSum) . ' руб';

            $this->botClient->sendMessage($message->getChat()->getId(), $textMessage);

            return false;
        };
    }

    /**
     * @return Closure
     */
    protected function getReceiveIncomesCommandCallback(): Closure
    {
        return function (Message $message) {
            $incomesCollection = $this->incomesRepository->findAll();
            $expensesCollection = $this->expensesRepository->findAll();

            $textMessage = '';
            $allIncomesSum = $allExpensesSum = 0;

            $incomesCollection->map(function (IncomesModel $model) use (&$textMessage, &$allIncomesSum) {
                $currency = $model->getCurrency() ?: CurrencyHelper::RUB_CURRENCY;
                $sum = $model->getAmount();

                switch ($currency) {
                    case CurrencyHelper::USD_CURRENCY: $sum *= 72.43; break;
                    case CurrencyHelper::ALL_CURRENCY: $sum *= 86.43; break;
                }

                $allIncomesSum += $sum;

                $textMessage .= 'Доход № ' . $model->getQueueableId() . PHP_EOL
                    . 'сумма - ' . $sum . PHP_EOL
                    . 'описание - ' . $model->getDescription() . PHP_EOL
                    . 'дата - ' . $model->getCreatedAt() . PHP_EOL . PHP_EOL;
            });

            $expensesCollection->map(function (ExpensesModel $model) use (&$allExpensesSum) {
                $currency = $model->getCurrency() ?: CurrencyHelper::RUB_CURRENCY;
                $sum = $model->getAmount();

                switch ($currency) {
                    case CurrencyHelper::USD_CURRENCY: $sum *= 72.43; break;
                    case CurrencyHelper::ALL_CURRENCY: $sum *= 86.43; break;
                }

                $allExpensesSum += $sum;
            });

            $incomesTotalAmount = PriceHelper::formatSumToString($allIncomesSum);
            $balanceAmount = PriceHelper::formatSumToString($allIncomesSum - $allExpensesSum);

            $textMessage .= 'Общая сумма дохода за весь период: ' . PHP_EOL
                . $incomesTotalAmount
                . ' руб' . PHP_EOL . PHP_EOL;

            $textMessage .= 'Остаток: ' . $balanceAmount . ' руб';

            $this->botClient->sendMessage($message->getChat()->getId(), $textMessage);

            return false;
        };
    }
}
