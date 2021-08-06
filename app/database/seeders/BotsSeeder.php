<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BotModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BotsSeeder extends Seeder
{
    protected const BOTS_DATA = [
        [
            'command' => 'send_expenses',
            'step_id' => 1,
            'next_step_id' => 2,
            'bot_step' => 'Отправьте сумму покупки:',
            'user_step' => 'set_amount',
        ],
        [
            'command' => 'send_expenses',
            'step_id' => 2,
            'next_step_id' => 3,
            'bot_step' => 'Выберите валютный курс:',
            'user_step' => 'set_currency',
        ],
        [
            'command' => 'send_expenses',
            'step_id' => 3,
            'next_step_id' => 4,
            'bot_step' => 'Отправьте количество:',
            'user_step' => 'set_count',
        ],
        [
            'command' => 'send_expenses',
            'step_id' => 4,
            'next_step_id' => 5,
            'bot_step' => 'Отправьте описание покупки:',
            'user_step' => 'set_description',
        ],
        [
            'command' => 'send_expenses',
            'step_id' => 5,
            'next_step_id' => null,
            'bot_step' => 'Данные сохранены!',
            'user_step' => '',
        ],
        [
            'command' => 'send_incomes',
            'step_id' => 1,
            'next_step_id' => 2,
            'bot_step' => 'Отправьте сумму прибыли:',
            'user_step' => 'set_amount',
        ],
        [
            'command' => 'send_incomes',
            'step_id' => 2,
            'next_step_id' => 3,
            'bot_step' => 'Выберите валютный курс:',
            'user_step' => 'set_currency',
        ],
        [
            'command' => 'send_incomes',
            'step_id' => 3,
            'next_step_id' => 4,
            'bot_step' => 'Отправьте описание:',
            'user_step' => 'set_description',
        ],
        [
            'command' => 'send_incomes',
            'step_id' => 4,
            'next_step_id' => null,
            'bot_step' => 'Данные сохранены!',
            'user_step' => '',
        ],
        [
            'command' => 'receive_expenses',
            'step_id' => 1,
            'next_step_id' => 2,
            'bot_step' => 'Выберите период:',
            'user_step' => 'set_period',
        ],
        [
            'command' => 'receive_expenses',
            'step_id' => 2,
            'next_step_id' => null,
            'bot_step' => '',
            'user_step' => '',
        ],
        [
            'command' => 'receive_incomes',
            'step_id' => 1,
            'next_step_id' => 2,
            'bot_step' => 'Выберите период:',
            'user_step' => 'set_period',
        ],
        [
            'command' => 'receive_expenses',
            'step_id' => 2,
            'next_step_id' => null,
            'bot_step' => '',
            'user_step' => '',
        ],
    ];

    public function run(): void
    {
        DB::table('bots')->delete();

        foreach (self::BOTS_DATA as $botData) {
            $botModel = new BotModel();

            $botModel
                ->setCommand($botData['command'])
                ->setStepId($botData['step_id'])
                ->setNextStepId($botData['next_step_id'])
                ->setBotStep($botData['bot_step'])
                ->setUserStep($botData['user_step']);

            $botModel->save();
        }
    }
}
