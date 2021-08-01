<?php

declare(strict_types=1);

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class BotInfoController extends Controller
{
    use ValidatesRequests, AuthorizesRequests;

    protected BotApi $botApi;
    protected BotApi $errorBotApi;

    /**
     * @param BotApi $botApi
     * @param BotApi $errorBotApi
     */
    public function __construct(BotApi $botApi, BotApi $errorBotApi)
    {
        $this->botApi = $botApi;
        $this->errorBotApi = $errorBotApi;
    }

    /**
     * @return Response
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function __invoke(): Response
    {
        $webhookInfo = $this->botApi->getWebhookInfo()->toJson(true);
        $botInfo = $this->botApi->getMe()->toJson(true);

        $errorWebhookInfo = $this->errorBotApi->getWebhookInfo()->toJson(true);
        $errorBotInfo = $this->errorBotApi->getMe()->toJson(true);

        $data = [
            'bot' => compact('webhookInfo', 'botInfo'),
            'error-bot' => compact('errorWebhookInfo', 'errorBotInfo')
        ];

        return new Response($data);
    }
}
