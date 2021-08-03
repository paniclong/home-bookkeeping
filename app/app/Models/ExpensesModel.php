<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpensesModel extends Model
{
    protected $table = 'expenses';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return (double) $this->getAttributeValue('amount');
    }

    /**
     * @param float $amount
     *
     * @return ExpensesModel
     */
    public function setAmount(float $amount): ExpensesModel
    {
        $this->setAttribute('amount', $amount);

        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return (int) $this->getAttributeValue('count');
    }

    /**
     * @param int $count
     *
     * @return ExpensesModel
     */
    public function setCount(int $count): ExpensesModel
    {
        $this->setAttribute('count', $count);

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return (string) $this->getAttributeValue('currency');
    }

    /**
     * @param string $currency
     *
     * @return ExpensesModel
     */
    public function setCurrency(string $currency): ExpensesModel
    {
        $this->setAttribute('currency', $currency);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string) $this->getAttributeValue('description');
    }

    /**
     * @param string $description
     *
     * @return ExpensesModel
     */
    public function setDescription(string $description): ExpensesModel
    {
        $this->setAttribute('description', $description);

        return $this;
    }

    /**
     * @param int|null $chatId
     *
     * @return ExpensesModel
     */
    public function setChatId(?int $chatId): ExpensesModel
    {
        $this->setAttribute('chat_id', $chatId);

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getAttributeValue('created_at');
    }
}
