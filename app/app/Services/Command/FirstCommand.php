<?php

declare(strict_types=1);

namespace App\Services\Command;

use App\Factory\MessageFactory;
use App\Helper\TelegramBotHelper;
use App\Repository\BotRepository;
use App\Repository\MessageRepository;
use App\Services\MessageTransformer;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

/**
 * Команда, которая отвечает за ведение диалога между юзером и ботом (для этого используется таблица messages & bots)
 */
class FirstCommand implements CommandInterface
{
    protected LoggerInterface $logger;
    protected MessageRepository $messageRepository;
    protected BotRepository $botRepository;
    protected MessageTransformer $messageTransformer;

    /**
     * @param LoggerInterface $logger
     * @param MessageRepository $messageRepository
     * @param BotRepository $botRepository
     * @param MessageTransformer $messageTransformer
     */
    public function __construct(
        LoggerInterface $logger,
        MessageRepository $messageRepository,
        BotRepository $botRepository,
        MessageTransformer $messageTransformer
    ) {
        $this->logger = $logger;
        $this->messageRepository = $messageRepository;
        $this->botRepository = $botRepository;
        $this->messageTransformer = $messageTransformer;
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

                $messageObject = $this->messageTransformer->transform($botModel->getBotStep(), $botModel);

                $client->sendMessage(
                    $chatId,
                    $messageObject->getText(),
                    $messageObject->getParseMode(),
                    $messageObject->isDisablePreview(),
                    $messageObject->getReplyToMessageId(),
                    $messageObject->getReplyMarkup(),
                    $messageObject->isDisableNotification()
                );

                $this->logger->info(sprintf('Success handle command - %s', $message->getText()));

                return false;
            }

            $this->logger->info('Nothing to send first message');

            return true;
        }, $checker);
    }
}
