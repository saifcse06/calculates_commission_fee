<?php

namespace App\Tests\Repositories;

use App\Models\TransactionModel;
use App\Repositories\TransactionRepository;
use PHPUnit\Framework\TestCase;

class TransactionRepositoryTest extends TestCase
{
    public function testSetGetAllData()
    {
        $transaction = new TransactionModel();
        $transaction->setOperationDate("2021-01-01");
        $transaction->setUserId("4");
        $transaction->setUserType("privet");
        $transaction->setOperationType("deposit");
        $transaction->setTransactionAmount("500.00");
        $transaction->setCurrency("JPY");

        $repo = new TransactionRepository();
        $repo->addData((object) $transaction);
        $this->assertEquals([$transaction], $repo->getAllData());
    }

}