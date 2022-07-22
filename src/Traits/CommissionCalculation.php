<?php

namespace App\Traits;

use App\Models\TransactionModel;

trait CommissionCalculation
{

    public function printCommission(array $commissionData)
    {
        $roundUp = $this->roundUp($commissionData['amount'], $commissionData['config'], $commissionData['precision']);
        fwrite(STDOUT, print_r($roundUp . "\n", true));
    }

    public function roundUp(float $amount, array $setting, int $precision)
    {
        $amount = bcmul($amount, (string)pow(10, $precision), $setting['commissionPrecision']);
        $parts  = explode('.', $amount);
        if (count($parts) == 2 && intval($parts[1]) > 0) {
            $parts[0] = bcadd($parts[0], '1', $setting['commissionPrecision']);
        }
        return bcdiv($parts[0], (string)pow(10, $precision), $precision);
    }

    private function convertCurrency(TransactionModel $transaction, array $setting, float $amount = -1)
    {
        if ($amount < 0) {
            return $transaction->getOperationAmount() / $setting['currencyConversion'][$transaction->getCurrency()]['rate'];
        } else {
            return $amount * $setting['currencyConversion'][$transaction->getCurrency()]['rate'];
        }
    }
}