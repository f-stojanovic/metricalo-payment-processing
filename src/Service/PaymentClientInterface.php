<?php

namespace App\Service;

use App\DTO\TransactionDTO;

interface PaymentClientInterface
{
    public function processPayment(
        float $amount,
        string $currency,
        string $cardNumber,
        string $cardExpMonth,
        string $cardExpYear,
        string $cardCvv,
        string $cardholderName
    ): TransactionDTO;
}