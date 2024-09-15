<?php

namespace App\Controller;

use App\DTO\PaymentRequestDTO;
use App\Manager\ACIClientManager;
use App\Manager\Shift4ClientManager;
use App\Service\PaymentService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly Shift4ClientManager $shift4ClientManager,
        private readonly AciClientManager $aciClientManager,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
    ) { }

    #[Route('/app/example/{provider}', name: 'payment')]
    public function sendPaymentRequest(Request $request, string $provider): JsonResponse
    {
        $paymentData = new PaymentRequestDTO($request->getPayload());

        $errors = $this->validator->validate($paymentData);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        try {
            $clientManager = match ($provider) {
                'shift4' => $this->shift4ClientManager,
                'aci' => $this->aciClientManager,
                default => throw new \RuntimeException('Invalid provider'),
            };

            $paymentService = new PaymentService($clientManager);
            $response = $paymentService->processPayment(
                $paymentData->amount,
                $paymentData->currency,
                $paymentData->cardNumber,
                $paymentData->expMonth,
                $paymentData->expYear,
                $paymentData->cvv,
                $paymentData->cardholderName
            );

            return new JsonResponse($response->toArray());

        } catch (\RuntimeException $e) {
            $this->logger->error('Payment processing failed', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }
}