<?php

namespace App\Tests\Unit;

use App\Manager\Shift4ClientManager;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Shift4ClientManagerTest extends TestCase
{
    const SHIFT4_API_KEY = 'sk_test_kJVebLOgZDyUHgr7A3ZDjJlW';
    private Shift4ClientManager $shift4ClientManager;
    private HttpClientInterface $httpClientMock;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $this->shift4ClientManager = new Shift4ClientManager($this->httpClientMock, $loggerMock, self::SHIFT4_API_KEY);
    }

    public function testProcessPayment()
    {
        $this->httpClientMock
            ->method('request')
            ->willReturn($this->createHttpClientResponseMock());

        $transactionDTO = $this->shift4ClientManager->processPayment(
            499, 'USD', '4242424242424242', '11', '2027', '123', 'John Doe'
        );

        $this->assertEquals('txn_1', $transactionDTO->getTransactionId());
        $this->assertEquals('2024-09-14 18:08:07', $transactionDTO->getDateCreated());
        $this->assertEquals(499, $transactionDTO->getAmount());
        $this->assertEquals('USD', $transactionDTO->getCurrency());
    }

    private function createHttpClientResponseMock(): ResponseInterface
    {
        // Mock a valid HTTP response
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')->willReturn(json_encode([
            'id' => 'txn_1',
            'created' => 1726337287,
            'amount' => 499,
            'currency' => 'USD',
            'card' => [
                'first6' => '424242',
            ]
        ]));
        return $responseMock;
    }
}
