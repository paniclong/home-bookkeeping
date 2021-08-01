<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Controllers\BotController;
use App\Http\Controllers\BotInfoController;
use App\Repository\BotsRepository;
use App\Repository\MessagesRepository;
use App\Services\BotExpensesService;
use App\Services\BotIncomesService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(BotController::class, static function (Container $app) {
            $client = $app->make(Client::class, ['token' => config('telegram.bot_id')]);
            $errorClient = $app->make(Client::class, ['token' => config('telegram.error_bot_id')]);

            return new BotController(
                $client,
                $errorClient,
                $app->make(LoggerInterface::class),
                $app->make(MessagesRepository::class),
                $app->make(BotsRepository::class),
                $app->make(BotExpensesService::class),
                $app->make(BotIncomesService::class)
            );
        });

        $this->app->bind(BotInfoController::class, static function (Container $app) {
            $botApi = $app->make(BotApi::class, ['token' => config('telegram.bot_id')]);
            $errorBotApi = $app->make(BotApi::class, ['token' => config('telegram.error_bot_id')]);

            return new BotInfoController($botApi, $errorBotApi);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
