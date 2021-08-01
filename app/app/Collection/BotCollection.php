<?php

declare(strict_types=1);

namespace App\Collection;

use App\Models\BotsModel;
use Illuminate\Database\Eloquent\Collection;

class BotCollection extends Collection
{
    /**
     * @param int $stepId
     *
     * @return BotsModel
     */
    public function findByStepId(int $stepId): BotsModel
    {
        return $this->firstWhere('step_id', '=', $stepId);
    }
}
