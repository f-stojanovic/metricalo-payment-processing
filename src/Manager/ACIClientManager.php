<?php

namespace App\Manager;

use App\DTO\TransactionDTO;
use App\Service\PaymentClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ACIClientManager implements PaymentClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly string $token,
    ) { }

    public function processPayment(
        float $amount,
        string $currency,
        string $cardNumber,
        string $cardExpMonth,
        string $cardExpYear,
        string $cardCvv,
        string $cardholderName
    ): TransactionDTO {
        $aciPaymentEndpoint = 'https://eu-test.oppwa.com/v1/payments';

        $data = http_build_query([
            'entityId' => '8a8294174b7ecb28014b9699220015ca',
            'amount' => $amount,
            'currency' => $currency,
            'paymentBrand' => 'VISA',
            'paymentType' => 'PA',
            'card.number' => $cardNumber,
            'card.holder' => $cardholderName,
            'card.expiryMonth' => $cardExpMonth,
            'card.expiryYear' => $cardExpYear,
            'card.cvv' => $cardCvv,
        ]);

        $response = $this->createRequest($aciPaymentEndpoint, $data);

        return new TransactionDTO(
            $response['id'] ?? '',
                \DateTime::createFromFormat('Y-m-d H:i:s.uO', $response['timestamp'])->format('Y-m-d H:i:s') ?? '',                $response['amount'] ?? '',
                $response['currency'] ?? '',
                $response['card']['bin'] ?? ''
        );
    }

    private function createRequest(string $endpoint, string $data): array
    {
        try {
            $response = $this->client->request('POST', $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $data,
            ]);


            return json_decode($response->getContent(), true) ?? [];
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('ACI request failed', [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'data' => $data,
            ]);
            throw new \RuntimeException('Failed to process ACI request', 0, $e);
        }
    }
}
