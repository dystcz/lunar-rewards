<?php

namespace Dystcz\LunarRewards\Domain\Rewards\Actions;

use Illuminate\Support\Facades\Config;
use Lunar\Models\Order;

class RewardPointsCalculator
{
    protected int|float $coefficient;

    public function __construct()
    {
        $this->setCoefficient(Config::get('lunar-rewards.reward_point_coefficient', 1));
    }

    /**
     * Set the coefficient for calculating reward points.
     */
    public function setCoefficient(int|float $coefficient): void
    {
        $this->coefficient = (float) $coefficient;
    }

    /**
     * Get the coefficient used for calculating reward points.
     */
    public function getCoefficient(): float
    {
        return $this->coefficient;
    }

    /**
     * Calculate the reward points for the given order.
     */
    public function handle(Order $order): int
    {
        $currency = $order->currency;
        $total = $order->total;
        $exchangeRate = $currency->exchange_rate;

        $points = (int) round($total * $exchangeRate * $this->coefficient);
    }
}
