<?php

declare(strict_types=1);

namespace App\Models;

use App\Collection\BotCollection;
use Illuminate\Database\Eloquent\Model;

class BotModel extends Model
{
    protected $table = 'bots';

    /**
     * @param string $command
     *
     * @return BotModel
     */
    public function setCommand(string $command): BotModel
    {
        $this->setAttribute('command', $command);

        return $this;
    }

    /**
     * @param int $stepId
     *
     * @return BotModel
     */
    public function setStepId(int $stepId): BotModel
    {
        $this->setAttribute('step_id', $stepId);

        return $this;
    }

    /**
     * @return string
     */
    public function getBotStep(): string
    {
        return (string) $this->getAttributeValue('bot_step');
    }

    /**
     * @param string $botStep
     *
     * @return BotModel
     */
    public function setBotStep(string $botStep): BotModel
    {
        $this->setAttribute('bot_step', $botStep);

        return $this;
    }

    /**
     * @return string
     */
    public function getUserStep(): string
    {
        return (string) $this->getAttributeValue('user_step');
    }

    /**
     * @param string $userStep
     *
     * @return BotModel
     */
    public function setUserStep(string $userStep): BotModel
    {
        $this->setAttribute('user_step', $userStep);

        return $this;
    }

    /**
     * @return int
     */
    public function getStepId(): int
    {
        return (int) $this->getAttributeValue('step_id');
    }

    /**
     * @return int|null
     */
    public function getNextStepId(): ?int
    {
        return $this->getAttributeValue('next_step_id');
    }

    /**
     * @param int|null $nextStepId
     *
     * @return BotModel
     */
    public function setNextStepId(?int $nextStepId): BotModel
    {
        $this->setAttribute('next_step_id', $nextStepId);

        return $this;
    }

    /**
     * @param array $models
     *
     * @return BotCollection
     */
    public function newCollection(array $models = []): BotCollection
    {
        return new BotCollection($models);
    }
}
