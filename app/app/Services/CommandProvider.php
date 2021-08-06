<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Command\CommandInterface;

class CommandProvider
{
    protected array $commands;

    /**
     * @param Command\CommandInterface $command
     */
    public function addCommand(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }

    /**
     * @return array|CommandInterface[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
