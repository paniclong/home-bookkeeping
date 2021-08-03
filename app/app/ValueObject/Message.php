<?php

declare(strict_types=1);

namespace App\ValueObject;

use TelegramBot\Api\Types\ForceReply;
use TelegramBot\Api\Types\ReplyKeyboardHide;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

class Message
{
    protected string $text;
    protected ?string $parseMode;
    protected bool $disablePreview;
    protected ?int $replyToMessageId;
    /**
     * @var ForceReply|ReplyKeyboardHide|ReplyKeyboardMarkup|ReplyKeyboardRemove|null
     */
    protected $replyMarkup;
    protected bool $disableNotification;

    /**
     * @param string $text
     * @param string|null $parseMode
     * @param bool $disablePreview
     * @param int|null $replyToMessageId
     * @param ReplyKeyboardMarkup|ReplyKeyboardHide|ForceReply|ReplyKeyboardRemove|null $replyMarkup
     * @param bool $disableNotification
     */
    public function __construct(
        string $text,
        string $parseMode = null,
        bool $disablePreview = false,
        int $replyToMessageId = null,
        $replyMarkup = null,
        bool $disableNotification = false
    ) {
        $this->text = $text;
        $this->parseMode = $parseMode;
        $this->disablePreview = $disablePreview;
        $this->replyToMessageId = $replyToMessageId;
        $this->replyMarkup = $replyMarkup;
        $this->disableNotification = $disableNotification;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string|null
     */
    public function getParseMode(): ?string
    {
        return $this->parseMode;
    }

    /**
     * @return bool
     */
    public function isDisablePreview(): bool
    {
        return $this->disablePreview;
    }

    /**
     * @return int|null
     */
    public function getReplyToMessageId(): ?int
    {
        return $this->replyToMessageId;
    }

    /**
     * @return ForceReply|ReplyKeyboardHide|ReplyKeyboardMarkup|ReplyKeyboardRemove|null
     */
    public function getReplyMarkup()
    {
        return $this->replyMarkup;
    }

    /**
     * @return bool
     */
    public function isDisableNotification(): bool
    {
        return $this->disableNotification;
    }
}
