<?php

namespace App\Tests\Integration;

use App\Manager\Shift4ClientManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Shift4ClientManagerIntegrationTest extends KernelTestCase
{
    private Shift4ClientManager $shift4ClientManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->shift4ClientManager = $container->get(Shift4ClientManager::class);
    }

    public function testProcessPaymentIntegration()
    {
        $transactionDTO = $this->shift4ClientManager->processPayment(
            499,
            'USD',
            '4242424242424242',
            '11',
            '2027', '123',
            'John Doe'
        );

        $this->assertNotNull($transactionDTO->getTransactionId());
        $this->assertEquals(499, $transactionDTO->getAmount());
        $this->assertEquals('USD', $transactionDTO->getCurrency());
    }
}
