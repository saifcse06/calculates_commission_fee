<?php
declare(strict_types=1);

namespace App\Repositories;

interface TransactionInterface
{
    public function setDataFromFile(string $fileName);

    public function getAllData(): array;

    public function addData(object $transactionModel);

    public function getByParam(string $param, int $value): array;

}