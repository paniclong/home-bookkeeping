<?php

declare(strict_types=1);

namespace App\Http\Controllers;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
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
     * @return View
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function __invoke(): View
    {
        return view(
            'bot-info',
            [
                'webhookInfo' => $this->botApi->getWebhookInfo()->toJson(true),
                'botInfo' => $this->botApi->getMe()->toJson(true),
                'errorWebhookInfo' => $this->errorBotApi->getWebhookInfo()->toJson(true),
                'errorBotInfo' => $this->errorBotApi->getMe()->toJson(true),
            ]
        );
    }
}
