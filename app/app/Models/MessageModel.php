<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageModel extends Model
{
    protected $table = 'messages';

    /**
     * @param int $chatId
     *
     * @return MessageModel
     */
    public function setChatId(int $chatId): self
    {
        $this->setAttribute('chat_id', $chatId);

        return $this;
    }

    /**
     * @param int $messageId
     *
     * @return MessageModel
     */
    public function setMessageId(int $messageId): self
    {
        $this->setAttribute('message_id', $messageId);

        return $this;
    }

    /**
     * @param int $step
     *
     * @return MessageModel
     */
    public function setStep(int $step): self
    {
        $this->setAttribute('step', $step);

        return $this;
    }

    /**
     * @param string $command
     *
     * @return MessageModel
     */
    public function setCommand(string $command): self
    {
        $this->setAttribute('command', $command);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommand(): ?string
    {
        return $this->getAttributeValue('command');
    }

    /**
     * @return int
     */
    public function getStep(): int
    {
        return (int)$this->getAttributeValue('step');
    }

    /**
     * @param int $botStep
     *
     * @return bool
     */
    public function isLastStep(int $botStep): bool
    {
        return $this->getStep() === $botStep;
    }
}
