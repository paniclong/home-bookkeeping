<?php

declare(strict_types=1);

namespace App\Helper;

class CurrencyHelper
{
    public const RUB_CURRENCY = 'RUB';
    public const USD_CURRENCY = 'USD';
    public const EUR_CURRENCY = 'EUR';

    public const ALL_CURRENCY = [
        self::RUB_CURRENCY,
        self::USD_CURRENCY,
        self::EUR_CURRENCY,
    ];
}
