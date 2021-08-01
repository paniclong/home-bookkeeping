<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BotsModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BotsSeeder extends Seeder
{
    protected const BOTS_DATA = [
        [
            'command' => 'send_expenses',
            'step_id' => 1,
            'bot_step' => 'Отправьте сумму покупки:',
            'user_step' => 'amount',
        ],
        [
            'command' => 'send_expenses',
            'step_id' => 2,
            'bot_step' => 'Отправьте количество:',
            'user_step' => 'count',
        ],
        [
            'command' => 'send_expenses',
            'step_id' => 3,
            'bot_step' => 'Отправьте описание покупки:',
            'user_step' => 'description',
        ],
        [
            'command' => 'send_expenses',
            'step_id' => 4,
            'bot_step' => 'Данные сохранены!',
            'user_step' => '',
        ],
        [
            'command' => 'send_incomes',
            'step_id' => 1,
            'bot_step' => 'Отправьте сумму прибыли:',
            'user_step' => 'amount',
        ],
        [
            'command' => 'send_incomes',
            'step_id' => 2,
            'bot_step' => 'Отправьте описание:',
            'user_step' => 'description',
        ],
        [
            'command' => 'send_incomes',
            'step_id' => 3,
            'bot_step' => 'Данные сохранены!',
            'user_step' => '',
        ],
    ];

    public function run(): void
    {
        DB::table('bots')->delete();

        foreach (self::BOTS_DATA as $botData) {
            $botsModel = new BotsModel();

            $botsModel
                ->setCommand($botData['command'])
                ->setStepId($botData['step_id'])
                ->setBotStep($botData['bot_step'])
                ->setUserStep($botData['user_step']);

            $botsModel->save();
        }
    }
}
