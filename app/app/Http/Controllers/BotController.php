<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CommandProvider;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\Client;
use Throwable;

class BotController extends Controller
{
    use ValidatesRequests;

    protected LoggerInterface $logger;
    protected Client $botClient;
    protected Client $errorBotClient;
    protected CommandProvider $commandProvider;

    /**
     * @param Client $botClient
     * @param Client $errorBotClient
     * @param LoggerInterface $logger
     * @param CommandProvider $commandProvider
     */
    public function __construct(
        Client             $botClient,
        Client             $errorBotClient,
        LoggerInterface    $logger,
        CommandProvider    $commandProvider
    ) {
        $this->botClient = $botClient;
        $this->errorBotClient = $errorBotClient;
        $this->logger = $logger;
        $this->commandProvider = $commandProvider;
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        try {
            $this->logger->info('Get webhook from telegram', ['raw_body' => $this->botClient->getRawBody()]);

            foreach ($this->commandProvider->getCommands() as $command) {
                $command->register($this->botClient);
            }

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
}
