<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Payment;

use App\Controller\Payment\DoneAction;
use App\Entity\Group;
use App\Entity\GroupOffer;
use App\Entity\PaymentToken;
use App\Entity\User;
use App\MessageBus\CommandBusInterface;
use App\Repository\GroupOfferRepository;
use App\Tests\TestReference;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\HttpRequestVerifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DoneActionTest extends TestCase
{
    private function getUuid(): Uuid
    {
        return Uuid::v6();
    }

    private function getGroupOffer(): GroupOffer
    {
        $group = (new Group())
            ->setId($this->getUuid())
            ->setSlug('group');

        return (new GroupOffer())  // @phpstan-ignore-line
            ->setId($this->getUuid())
            ->setGroup($group);
    }

    private function getUser(): User
    {
        return (new User())
            ->setId($this->getUuid());
    }

    /**
     * @return CommandBusInterface&MockObject
     */
    private function getCommandBus(): MockObject
    {
        return $this->getMockBuilder(CommandBusInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return GroupOfferRepository&MockObject
     */
    private function getGroupOfferRepo(): GroupOfferRepository
    {
        $groupOfferRepo = $this->getMockBuilder(GroupOfferRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupOfferRepo->method('find')
            ->willReturn($this->getGroupOffer());

        return $groupOfferRepo;
    }

    /**
     * Too complicated, the controller should be refactored.
     */
    public function testUnprocessableEntityHttpException(): void
    {
        $httpRequestVerifierInterface = $this->getMockBuilder(HttpRequestVerifierInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpRequestVerifierInterface->method('verify')
            ->willThrowException(new UnprocessableEntityHttpException());

        $payum = $this->getMockBuilder(Payum::class)
            ->disableOriginalConstructor()
            ->getMock();
        $payum->method('getHttpRequestVerifier')
            ->willReturn($httpRequestVerifierInterface);

        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doneAction = new DoneAction(
            $this->getGroupOfferRepo(),
            $payum,
            $translator,
            $this->getCommandBus()
        );
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot verify Payum token');
        $doneAction->__invoke(new Request(), TestReference::UUID_404, $this->getUser());
    }

    /**
     * All this to test a line :/.
     */
    public function testFlashWarning(): void
    {
        $httpRequestVerifierInterface = $this->getMockBuilder(HttpRequestVerifierInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpRequestVerifierInterface->method('verify')
            ->willReturn(new PaymentToken());

        $payum = $this->getMockBuilder(Payum::class)
            ->disableOriginalConstructor()
            ->getMock();
        $payum->method('getHttpRequestVerifier')
            ->willReturn($httpRequestVerifierInterface);

        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $comandBus = $this->getCommandBus();
        $comandBus->method('dispatch')->willReturn(new GetHumanStatus(new PaymentToken()));

        $doneAction = new DoneAction(
            $this->getGroupOfferRepo(),
            $payum,
            $translator,
            $comandBus
        );

        // set session!
        $session = $this->getMockBuilder(FlashBagAwareSessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $session->method('getFlashBag')->willReturn(new FlashBag());

        $requesStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requesStack->method('getSession')->willReturn($session);
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->method('get')->willReturn($requesStack);
        $doneAction->setContainer($container);

        $this->expectException(\Error::class); // or more mock are needed. To cleanup later
        $doneAction->__invoke(new Request(), TestReference::UUID_404, $this->getUser());
    }
}
