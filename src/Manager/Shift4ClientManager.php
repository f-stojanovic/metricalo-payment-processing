<?php

namespace App\Manager;

use App\DTO\TransactionDTO;
use App\Service\PaymentClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Shift4ClientManager implements PaymentClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly string $apiKey,
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
        $shift4ChargeEndpoint = 'https://api.shift4.com/charges';

        $customerToken = $this->getToken($cardNumber, $cardExpMonth, $cardExpYear, $cardCvv, $cardholderName);
        $customer = $this->getCustomer($customerToken);

        $paymentToken = $this->getToken($cardNumber, $cardExpMonth, $cardExpYear, $cardCvv, $cardholderName);

        $data = [
            'amount' =>$amount,
            'currency' => $currency,
            'customerId' => $customer,
            'card' => $paymentToken,
            'description' => 'Charge description'
        ];

        $response =  $this->createRequest($shift4ChargeEndpoint, $data);

        return new TransactionDTO(
            $response['id'] ?? '',
                \DateTime::createFromFormat('U', $response['created'])->format('Y-m-d H:i:s') ?? '',
            $response['amount'] ?? '',
            $response['currency'] ?? '',
            $response['card']['first6'] ?? ''
        );
    }

    public function getToken(
        string $cardNumber,
        string $cardExpMonth,
        string $cardExpYear,
        string $cardCvv,
        string $cardholderName
    ): string {
        $shift4TokenEndpoint = 'https://api.shift4.com/tokens';

        $data = [
            'number' => $cardNumber,
            'expMonth' => $cardExpMonth,
            'expYear' => $cardExpYear,
            'cvc' => $cardCvv,
            'cardholderName' => $cardholderName,
        ];

        $response = $this->createRequest($shift4TokenEndpoint, $data);

        if (isset($response['id'])) {
            return $response['id'];
        }

        throw new \RuntimeException('Token generation failed');
    }

    public function getCustomer(string $token): string
    {
        $shift4CustomerEndpoint = 'https://api.shift4.com/customers';

        $data = [
            'email' => 'john@doe.com',
            'card' => $token
        ];

        $response = $this->createRequest($shift4CustomerEndpoint, $data);

        if (isset($response['id'])) {
            return $response['id'];

        }

        throw new \RuntimeException('Customer creation failed');
    }

    private function createRequest(string $endpoint, array $data): array
    {
        try {
            $response = $this->client->request('POST', $endpoint, [
                'auth_basic' => [$this->apiKey, ''],
                'json' => $data,
            ]);
            return json_decode($response->getContent(), true) ?? [];
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Shift4 request failed', [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'data' => $data,
            ]);
            throw new \RuntimeException('Failed to process Shift4 request', 0, $e);
        }
    }
}
