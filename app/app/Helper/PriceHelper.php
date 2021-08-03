<?php

declare(strict_types=1);

namespace App\Helper;

class PriceHelper
{
    /**
     * @param float $sum
     *
     * @return string
     */
    public static function formatSumToString(float $sum): string
    {
        return number_format($sum, 0, '', ' ');
    }
}
