<?php

declare(strict_types=1);

namespace App\Collection;

use App\Models\BotModel;
use Illuminate\Database\Eloquent\Collection;

class BotCollection extends Collection
{
    /**
     * @param int $stepId
     *
     * @return BotModel
     */
    public function findOneByStepId(int $stepId): BotModel
    {
        return $this->firstWhere('step_id', '=', $stepId);
    }

    /**
     * @param int $stepId
     *
     * @return bool
     */
    public function isLastStep(int $stepId): bool
    {
        /** @var BotModel $botModel */
        $botModel = $this->firstWhere('step_id', '=', $stepId);

        return $botModel->getNextStepId() === null;
    }

    /**
     * @return BotModel
     */
    public function getFirst(): BotModel
    {
        return $this->firstWhere('step_id', '=', 1);
    }
}
