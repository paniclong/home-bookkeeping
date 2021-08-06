<?php

declare(strict_types=1);

namespace App\Services;

use App\Collection\BotCollection;
use App\Helper\CurrencyHelper;
use App\Helper\PriceHelper;
use App\Models\ExpensesModel;
use App\Models\IncomesModel;
use App\Models\MessageModel;
use App\Repository\ExpensesRepository;
use App\Repository\IncomesRepository;
use TelegramBot\Api\Types\Message;

class BotStepService
{
    protected MessageTransformer $messageTransformer;
    protected ExpensesRepository $expensesRepository;
    protected IncomesRepository $incomesRepository;

    /**
     * @param MessageTransformer $messageTransformer
     * @param ExpensesRepository $expensesRepository
     * @param IncomesRepository $incomesRepository
     */
    public function __construct(MessageTransformer $messageTransformer, ExpensesRepository $expensesRepository, IncomesRepository $incomesRepository)
    {
        $this->messageTransformer = $messageTransformer;
        $this->expensesRepository = $expensesRepository;
        $this->incomesRepository = $incomesRepository;
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

        if ($botModel->getNextStepId() === null) {
            $messageModel->delete();
        } else {
            $messageModel->setStep($botModel->getNextStepId());
            $messageModel->save();

            $botModel = $botCollection->findOneByStepId($botModel->getNextStepId());
        }

        $model->save();

        return $this->messageTransformer->transform($botModel->getBotStep(), $botModel);
    }

    /**
     * @param Message $telegramMessage
     * @param MessageModel $messageModel
     * @param BotCollection $botCollection
     *
     * @return \App\ValueObject\Message
     */
    public function handleNoEntityCommand(Message $telegramMessage, MessageModel $messageModel, BotCollection $botCollection): \App\ValueObject\Message
    {
        $text = $telegramMessage->getText();
        $step = $messageModel->getStep();

        $botModel = $botCollection->findOneByStepId($step);

        switch ($botModel->getUserStep()) {
            case 'receive_expenses':
                $text = $this->getTextForActionReceiveExpenses();
                break;
            case 'receive_incomes':
                $text = $this->getTextForActionReceiveIncomes();
                break;
        }

        if ($botModel->getNextStepId() === null) {
            $messageModel->delete();
        } else {
            $messageModel->setStep($botModel->getNextStepId());
            $messageModel->save();

            $botModel = $botCollection->findOneByStepId($botModel->getNextStepId());
        }

        return $this->messageTransformer->transform($text, $botModel);
    }

    /**
     * @return string
     */
    protected function getTextForActionReceiveExpenses(): string
    {
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

        return $textMessage;
    }

    /**
     * @return string
     */
    protected function getTextForActionReceiveIncomes(): string
    {
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

        return $textMessage;
    }
}
