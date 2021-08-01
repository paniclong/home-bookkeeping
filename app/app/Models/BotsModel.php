<?php

declare(strict_types=1);

namespace App\Models;

use App\Collection\BotCollection;
use Illuminate\Database\Eloquent\Model;

class BotsModel extends Model
{
    protected $table = 'bots';

    /**
     * @param string $command
     *
     * @return BotsModel
     */
    public function setCommand(string $command): BotsModel
    {
        $this->setAttribute('command', $command);

        return $this;
    }

    /**
     * @param int $stepId
     *
     * @return BotsModel
     */
    public function setStepId(int $stepId): BotsModel
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
     * @return BotsModel
     */
    public function setBotStep(string $botStep): BotsModel
    {
        $this->setAttribute('bot_step', $botStep);

        return $this;
    }

    /**
     * @param string $userStep
     *
     * @return BotsModel
     */
    public function setUserStep(string $userStep): BotsModel
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
     * @param array $models
     *
     * @return BotCollection
     */
    public function newCollection(array $models = []): BotCollection
    {
        return new BotCollection($models);
    }
}
