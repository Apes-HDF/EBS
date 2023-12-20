<?php

declare(strict_types=1);

namespace App\Tests\Integration\Workflow;

use App\Entity\ServiceRequest;
use App\Enum\ServiceRequest\ServiceRequestStatus;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use App\Workflow\ServiceRequestStatusWorkflow;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ServiceRequestStatusWorkflowTest extends KernelTestCase
{
    use ContainerRepositoryTrait;

    private function getServiceRequestStatusWorkflow(): ServiceRequestStatusWorkflow
    {
        /** @var ServiceRequestStatusWorkflow $serviceRequestStatusWorkflow */
        $serviceRequestStatusWorkflow = self::getContainer()->get(ServiceRequestStatusWorkflow::class);

        return $serviceRequestStatusWorkflow;
    }

    private function getServiceRequest(): ServiceRequest
    {
        $sr = $this->getServiceRequestRepository()->get(TestReference::SERVICE_REQUEST_1);
        $sr->setStatus(ServiceRequestStatus::NEW);

        return $sr;
    }

    public function testAcceptTransitionSuccess(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest();

        self::assertTrue($serviceRequestStatusWorkflow->canAccept($sr));
        self::assertSame(ServiceRequestStatus::TO_CONFIRM, $serviceRequestStatusWorkflow->accept($sr)->getStatus());
    }

    public function testAcceptTransitionException(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest();

        $sr->setStatus(ServiceRequestStatus::FINISHED);
        self::assertFalse($serviceRequestStatusWorkflow->canAccept($sr));
        $this->expectException(\LogicException::class);
        $serviceRequestStatusWorkflow->accept($sr);
    }

    public function testModifyOwnerTransitionSuccess(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest();

        self::assertTrue($serviceRequestStatusWorkflow->canModifyOwner($sr));
        self::assertSame(ServiceRequestStatus::TO_CONFIRM, $serviceRequestStatusWorkflow->modifyOwner($sr)->getStatus());
    }

    public function testModifyOwnerTransitionException(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest()
            ->setStatus(ServiceRequestStatus::FINISHED);
        self::assertFalse($serviceRequestStatusWorkflow->canModifyRecipient($sr));
        $this->expectException(\LogicException::class);
        $serviceRequestStatusWorkflow->modifyRecipient($sr);
    }

    public function testModifyRecipientTransitionSuccess(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest()
            ->setStatus(ServiceRequestStatus::TO_CONFIRM);

        self::assertTrue($serviceRequestStatusWorkflow->canModifyRecipient($sr));
        self::assertSame(ServiceRequestStatus::NEW, $serviceRequestStatusWorkflow->modifyRecipient($sr)->getStatus());
    }

    public function testModifyRecipientTransitionException(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest();

        $sr->setStatus(ServiceRequestStatus::FINISHED);
        self::assertFalse($serviceRequestStatusWorkflow->canModifyRecipient($sr));
        $this->expectException(\LogicException::class);
        $serviceRequestStatusWorkflow->modifyOwner($sr);
    }

    public function testConfirmTransitionSuccess(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest()
            ->setStatus(ServiceRequestStatus::TO_CONFIRM);

        self::assertTrue($serviceRequestStatusWorkflow->canConfirm($sr));
        self::assertSame(ServiceRequestStatus::CONFIRMED, $serviceRequestStatusWorkflow->confirm($sr)->getStatus());
    }

    public function testConfirmTransitionException(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest();

        $sr->setStatus(ServiceRequestStatus::FINISHED);
        self::assertFalse($serviceRequestStatusWorkflow->canConfirm($sr));
        $this->expectException(\LogicException::class);
        $serviceRequestStatusWorkflow->confirm($sr);
    }

    public function testRefuseTransitionSuccess(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest()
            ->setStatus(ServiceRequestStatus::NEW);

        self::assertTrue($serviceRequestStatusWorkflow->canRefuse($sr));
        self::assertSame(ServiceRequestStatus::REFUSED, $serviceRequestStatusWorkflow->refuse($sr)->getStatus());
    }

    public function testRefuseTransitionException(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest();

        $sr->setStatus(ServiceRequestStatus::FINISHED);
        self::assertFalse($serviceRequestStatusWorkflow->canRefuse($sr));
        $this->expectException(\LogicException::class);
        $serviceRequestStatusWorkflow->refuse($sr);
    }

    public function testFinalizeTransitionSuccess(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest()
            ->setStatus(ServiceRequestStatus::CONFIRMED)
            ->setStartAt(new \DateTimeImmutable('yesterday'))
            ->setEndAt(new \DateTimeImmutable('today'))
        ;

        self::assertTrue($serviceRequestStatusWorkflow->canFinalize($sr));
        self::assertSame(ServiceRequestStatus::FINISHED, $serviceRequestStatusWorkflow->finalize($sr)->getStatus());
    }

    public function testFinalizeTransitionException(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest();

        self::assertFalse($serviceRequestStatusWorkflow->canFinalize($sr));
        $this->expectException(\LogicException::class);
        $serviceRequestStatusWorkflow->finalize($sr);
    }

    public function testAutoFinalizeTransitionSuccess(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest()
            ->setStatus(ServiceRequestStatus::CONFIRMED)
            ->setStartAt(new \DateTimeImmutable('- 5 days'))
            ->setEndAt(new \DateTimeImmutable('- 3 days'))
        ;

        self::assertTrue($serviceRequestStatusWorkflow->canAutoFinalize($sr));
        self::assertSame(ServiceRequestStatus::FINISHED, $serviceRequestStatusWorkflow->autoFinalize($sr)->getStatus());
    }

    /**
     * Ongoing service request that can't be auto-finalized.
     *
     * @see ServiceRequestAutoFinalizeTransitionSubscriber
     */
    public function testAutoFinalizeTransitionException(): void
    {
        self::bootKernel();
        $serviceRequestStatusWorkflow = $this->getServiceRequestStatusWorkflow();
        $sr = $this->getServiceRequest()
            ->setStatus(ServiceRequestStatus::CONFIRMED)
            ->setStartAt(new \DateTimeImmutable('- 3 days'))
            ->setEndAt(new \DateTimeImmutable('+ 3 days'))
        ;
        self::assertFalse($serviceRequestStatusWorkflow->canAutoFinalize($sr));
        $this->expectException(\LogicException::class);
        $serviceRequestStatusWorkflow->autoFinalize($sr);
    }
}
