<?php

declare(strict_types=1);

namespace App\Doctrine\Manager;

use App\Entity\Product;
use App\Entity\ProductAvailability;
use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Repository\ServiceRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\String\u;

final class ServiceRequestManager
{
    public const DOMAIN = 'messages_system';
    public const TRANS_PREFIX = 'src.doctrine.manager.service_request_manager';

    public function __construct(
        private readonly ServiceRequestRepository $serviceRequestRepository,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(ServiceRequest $entity, bool $flush = false): void
    {
        $this->serviceRequestRepository->save($entity, $flush);
    }

    public function readMessages(ServiceRequest $serviceRequest, User $user): void
    {
        foreach ($serviceRequest->getMessages() as $message) {
            if ($serviceRequest->isOwner($user)) {
                $message->setOwnerRead(true);
                $message->setOwnerReadAt(new \DateTimeImmutable('now'));
            } else {
                $message->setRecipientRead(true);
                $message->setRecipientReadAt(new \DateTimeImmutable('now'));
            }
        }

        $this->save($serviceRequest, true);
    }

    /**
     * Initialize a new service request for a product with sensible default values.
     */
    public function initFormProductAndRequest(Product $product, Request $request): ServiceRequest
    {
        $serviceRequest = (new ServiceRequest())
            ->setMessage($this->translator->trans(
                self::TRANS_PREFIX.'.message.'.$product->getType()->value.'.default',
                ['%product%' => u($product->getName())->lower()->toString()],
            ))
            ->setProduct($product)
        ;

        $startAt = $request->query->getAlnum('startAt');
        if (!u($startAt)->isEmpty()) {
            try {
                $serviceRequest->setStartAt(new \DateTimeImmutable($startAt));
            } catch (\Exception) {
            }
        }
        $endAt = $request->query->getAlnum('endAt');
        if (!u($endAt)->isEmpty()) {
            try {
                $serviceRequest->setEndAt(new \DateTimeImmutable($endAt));
            } catch (\Exception) {
            }
        }

        return $serviceRequest;
    }

    /**
     * Delete all unavailabilities linked to the service request.
     */
    public function deleteUnavailabilities(ServiceRequest $serviceRequest): void
    {
        $toDelete = $serviceRequest->getProduct()->getAvailabilities()->filter(
            fn (ProductAvailability $productAvailability) => $productAvailability->getServiceRequest() === $serviceRequest
        );
        foreach ($toDelete as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();
    }
}
