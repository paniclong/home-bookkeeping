<?php

declare(strict_types=1);

namespace App\Services\Command;

use TelegramBot\Api\Client;

interface CommandInterface
{
    /**
     * @param Client $client
     */
    public function register(Client $client): void;
}
