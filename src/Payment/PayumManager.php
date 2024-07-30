<?php

declare(strict_types=1);

namespace App\Payment;

use App\Controller\Payment\Group\PrepareAction;
use App\Entity\GroupOffer;
use App\Entity\Payment;
use App\Entity\PlatformOffer;
use App\Entity\User;
use App\Enum\Payment\PaymentMethod;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Centralize all Payum proccessing.
 */
final class PayumManager
{
    public function __construct(
        private readonly Payum $payum,
        #[Autowire('%env(string:PAYUM_GATEWAY)%')] private readonly string $payumGateway
    ) {
    }

    /**
     * Create a new payment from a group offer and a user (+ persist in db).
     *
     * @see PrepareAction
     */
    public function getPayment(GroupOffer|PlatformOffer $offer, User $user): Payment
    {
        $storage = $this->payum->getStorage(Payment::class);
        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setNumber(uniqid('payum_', true));
        $payment->setCurrencyCode($offer->getCurrency());
        $payment->setTotalAmount($offer->getPrice());

        if ($offer instanceof GroupOffer) {
            $payment->setDescription($offer->getGroup()->getName().' / '.$offer->getName());
        }

        if ($offer instanceof PlatformOffer) {
            $payment->setDescription($offer->getConfiguration()?->getPlatformName().' / '.$offer->getName());
        }

        $payment->setClientId((string) $user->getId());
        $payment->setClientEmail($user->getEmail());
        $payment->setUser($user);
        $payment->setDetails($this->getGatewayDetails($offer));
        $storage->update($payment);

        return $payment;
    }

    /**
     * Add specific details to the current gateway. Put here any fields in a gateway format.
     * For now this function is specific to Mollie.
     *
     * For example if you use Paypal ExpressCheckout you can define a description of the first item:
     *    'L_PAYMENTREQUEST_0_DESC0' => 'A desc'
     *
     * @todo Check if the default method can be retrieved from the gateway configuration.
     *
     * @see https://github.com/webbaard/payum-mollie/blob/master/Resources/doc/checkout_mollie.md
     *
     * @return array<string, mixed>
     */
    private function getGatewayDetails(GroupOffer|PlatformOffer $offer): array
    {
        if ($offer instanceof GroupOffer) {
            return [
                // method must be set as the default value is not retrieved from the gateway configuration
                'method' => PaymentMethod::CREDITCARD->value,
                'metadata' => [
                    'groupId' => (string) $offer->getGroup()->getId(),
                    'groupOfferId' => (string) $offer->getId(),
                ],
            ];
        } else {
            return [
                // method must be set as the default value is not retrieved from the gateway configuration
                'method' => PaymentMethod::CREDITCARD->value,
                'metadata' => [
                    'platformId' => (string) $offer->getConfiguration()?->getId(),
                    'platformOfferId' => (string) $offer->getId(),
                ],
            ];
        }
    }

    /**
     * @param array<string, string> $afterParameters
     *
     * @see PrepareAction
     */
    public function getCaptureToken(Payment $payment, string $afterRoute, array $afterParameters): TokenInterface
    {
        return $this->payum->getTokenFactory()->createCaptureToken(
            $this->payumGateway,
            $payment,
            $afterRoute,
            $afterParameters
        );
    }
}
