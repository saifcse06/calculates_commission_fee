<?php
declare(strict_types=1);

namespace App\Service;

use App\Models\TransactionModel;
use App\Repositories\TransactionInterface;
use App\Traits\CommissionCalculation;

class MoneyTransactionServices
{
    use CommissionCalculation;

    /**
     * @var object
     */
    protected $transactionRepository;

    /**
     * @var array
     */
    protected $config;

    public function __construct(TransactionInterface $transactionRepository, array $config)
    {
        $this->transactionRepository = $transactionRepository;
        $this->config                = $config;
    }

    public function index(string $filename)
    {
        $this->transactionRepository->setDataFromFile($filename);
        $this->checkCommission($this->transactionRepository->getAllData());
    }

    private function checkCommission(array $transactions)
    {
        if (count($transactions) > 0) {
            foreach ($transactions as $transaction) {
                if ($transaction->getOperationType() == TransactionModel::DEPOSIT) {
                    $commission = $this->depositCommission($transaction, $this->config);
                } else {
                    $commission = $this->withdrawCommission($transaction, $this->config);
                }
                $commissionData['amount']  = $commission;
                $commissionData['config'] = $this->config;
                $commissionData['precision'] = $this->config['currencyConversion'][$transaction->getCurrency()]['precision'] or null;
                $this->printCommission($commissionData);
            }
        } else {
            print_r("Data Not Found\n");
        }
    }

    private function depositCommission(TransactionModel $transaction, array $setting)
    {
        $commission = $transaction->getOperationAmount() * $setting['depositCommissionPercent'];
        return $this->convertCurrency($transaction, $setting, $commission);
    }

    private function withdrawCommission(TransactionModel $transaction, array $setting)
    {
        if ($transaction->getUserType() == TransactionModel::PRIVETUSER) {
            $week                      = date("oW", strtotime($transaction->getOperationDate()));
            $userTransactions          = $this->transactionRepository->getByParam('userId', $transaction->getUserId());
            $transactionsPerWeek       = 0;
            $transactionsPerWeekAmount = 0;

            foreach ($userTransactions as $userTransaction) {
                $currentDate = date("oW", strtotime($userTransaction->getOperationDate()));
                if (
                    $week == $currentDate &&
                    $userTransaction->getOperationType() == TransactionModel::WITHDRAW
                ) {
                    if ($userTransaction->getId() == $transaction->getId()) {
                        break;
                    }
                    $transactionsPerWeek++;
                    $transactionsPerWeekAmount += $this->convertCurrency($userTransaction, $setting);
                }
            }

            if ($transactionsPerWeek >= $setting['withdrawCommissionCommonFreeTransactionsLimit']) {
                return $transaction->getOperationAmount() * $setting['user_types'][$transaction->getUserType()]['withdrawCommissionPercent'];
            } elseif ($transactionsPerWeekAmount >= $setting['withdrawCommissionCommonDiscount']) {
                return $transaction->getOperationAmount() * $setting['user_types'][$transaction->getUserType()]['withdrawCommissionPercent'];
            } else {
                $amount     = max(
                    $this->convertCurrency(
                        $transaction,
                        $setting
                    ) + $transactionsPerWeekAmount - $setting['withdrawCommissionCommonDiscount'],
                    0
                );
                $commission = $amount * $setting['user_types'][$transaction->getUserType()]['withdrawCommissionPercent'];
                return $this->convertCurrency($transaction, $setting, $commission);
            }
        } else {
            $commission = $transaction->getOperationAmount() * $setting['user_types'][$transaction->getUserType()]['withdrawCommissionPercent'];
            return $this->convertCurrency($transaction, $setting, $commission);
        }
    }

}