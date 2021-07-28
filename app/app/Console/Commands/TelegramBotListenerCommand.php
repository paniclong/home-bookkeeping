<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TelegramBot\Api\BotApi;

class TelegramBotListenerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listener:telegram-bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обработчик сообщений от пользоватея к боту телеграма';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $bot = new BotApi('1877197075:AAGqDO0bGa7iRG6FHjHv7yVd9l6PK7c0vvc');

//        var_dump($bot->getChat('451296313')->getPermissions());
//        die;

        $permissions = new \TelegramBot\Api\Types\ChatPermissions();
        $permissions->setCanSendMessages(true);
        $permissions->setCanSendMediaMessages(true);
        $permissions->setCanSendOtherMessages(true);

        $bot->getChat('451296313')->setPermissions($permissions);
//        die;

        $url = $bot->getWebhookInfo()->getUrl();

        $bot->deleteWebhook();

        foreach ($bot->getUpdates() as $update) {
            $result = $bot->deleteMessage($update->getMessage()->getChat()->getId(), $update->getMessage()->getMessageId());
            var_dump($result);
        }

        $bot->setWebhook($url);

        return 0;
    }
}
