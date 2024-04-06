<?php

namespace Dystcz\LunarRewards\Domain\Rewards\Actions;

use Dystcz\LunarRewards\Domain\Rewards\Contracts\Rewardable;
use Dystcz\LunarRewards\Domain\Rewards\DataTypes\Reward;
use O21\LaravelWallet\Models\Transaction;

class DepositPoints extends PointsAction
{
    /**
     * Deposit points to the given rewardable model.
     */
    public function handle(Rewardable $to, Reward $amount): Transaction
    {
        $transaction = $this->transactionCreator
            ->amount($amount->value)
            ->currency($amount->currency->code)
            ->processor('deposit')
            ->to($to)
            ->overcharge()
            ->commit();

        return $transaction;
    }
}
