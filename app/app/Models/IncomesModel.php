<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomesModel extends Model
{
    protected $table = 'incomes';

    protected $casts = [
        'amount' => 'double',
        'currency' => 'string',
        'description' => 'string',
        'created_at' => 'string',
    ];

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->getAttributeValue('amount');
    }

    /**
     * @param float $amount
     *
     * @return IncomesModel
     */
    public function setAmount(float $amount): IncomesModel
    {
        $this->setAttribute('amount', $amount);

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->getAttributeValue('currency');
    }

    /**
     * @param string $currency
     *
     * @return IncomesModel
     */
    public function setCurrency(string $currency): IncomesModel
    {
        $this->setAttribute('currency', $currency);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getAttributeValue('description');
    }

    /**
     * @param string $description
     *
     * @return IncomesModel
     */
    public function setDescription(string $description): IncomesModel
    {
        $this->setAttribute('description', $description);

        return $this;
    }

    /**
     * @param int|null $chatId
     *
     * @return IncomesModel
     */
    public function setChatId(?int $chatId): IncomesModel
    {
        $this->setAttribute('chat_id', $chatId);

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getAttributeValue('created_at');
    }
}
