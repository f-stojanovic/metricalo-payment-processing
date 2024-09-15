<?php

namespace App\Service;

use App\DTO\TransactionDTO;

class PaymentService
{
    public function __construct(
        private readonly PaymentClientInterface $paymentClient
    ) { }

    public function processPayment(
        float $amount,
        string $currency,
        string $cardNumber,
        string $expMonth,
        string $expYear,
        string $cvv,
        string $cardholderName
    ): TransactionDTO {
        return $this->paymentClient->processPayment(
            $amount,
            $currency,
            $cardNumber,
            $expMonth,
            $expYear,
            $cvv,
            $cardholderName
        );
    }
}
