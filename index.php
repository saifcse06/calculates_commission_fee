<?php
declare(strict_types=1);

use App\Repositories\TransactionRepository;
use App\Service\MoneyTransactionServices;

require_once __DIR__ . '/vendor/autoload.php';

if ($argc != 2) {
    die('File not found');
}

$setting = include('config.php');

try {
    $transactionResult = new MoneyTransactionServices(new TransactionRepository(), $setting);
    $transactionResult->index($argv[1]);
} catch (\Exception $ex) {
    echo $ex->getMessage();
    exit();
}

