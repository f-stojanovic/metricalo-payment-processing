<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\ProcessPaymentCommand;

class ProcessPaymentCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $command = $container->get(ProcessPaymentCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    public function testProcessPaymentCommand()
    {
        $this->commandTester->execute([
            'provider' => 'shift4',
            'amount' => 499,
            'currency' => 'USD',
            'cardNumber' => '4242424242424242',
            'expMonth' => '11',
            'expYear' => '2027',
            'cvv' => '123',
            'cardholderName' => 'John Doe'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Payment processed successfully', $output);
    }
}
