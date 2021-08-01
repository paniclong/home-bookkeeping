<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TelegramBot\Api\BotApi;

class TelegramBotSetWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:bot:webhook:set {--url=} {--bot_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Смена url для вебхука';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $url = $this->option('url');
        $botId = $this->option('bot_id');

        if ($url === null) {
            $this->error('Url is empty');

            return 0;
        }

        $botId = $botId ?: config('telegram.bot_id');

        try {
            $botApi = new BotApi($botId);

            if ($botApi->setWebhook($url)) {
                $this->info(sprintf('Success set webhook. Url - %s', $url));
            }
        } catch (\Throwable $ex) {
            $this->error(sprintf('Message - %s, Trace - %s', $ex->getMessage(), $ex->getTraceAsString()));
        }

        return 0;
    }
}
