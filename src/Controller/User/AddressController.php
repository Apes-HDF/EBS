<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\FlashTrait;
use App\Controller\SecurityTrait;
use App\Dto\User\UserAddressStep1Data;
use App\Entity\Address;
use App\Entity\User;
use App\Form\Type\User\AddressStep1FormType;
use App\Form\Type\User\AddressStep2FormType;
use App\Message\Command\User\UpdateAddressCommand;
use App\Message\Query\Admin\User\UserAddressQuery;
use App\MessageBus\CommandBus;
use App\MessageBus\QueryBus;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Nominatim\Model\NominatimAddress;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see AddressControllerTest
 */
final class AddressController extends AbstractController
{
    use FlashTrait;
    use SecurityTrait;

    private const STEP1_ROUTE = 'user_address_step1';
    private const STEP2_ROUTE = 'user_address_step2';
    private const STEP1_DATA_KEY = self::STEP1_ROUTE.'_data';

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    /**
     * @see UserAddressQueryHandler
     */
    #[isGranted(User::ROLE_USER)]
    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-address/step-1',
        'fr' => MyAccountAction::BASE_URL_FR.'/mon-adresse/etape-1',
    ], name: self::STEP1_ROUTE)]
    public function step1(Request $request, SessionInterface $session): Response
    {
        $form = $this->createForm(AddressStep1FormType::class, $this->getAppUser()->getAddress())->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Address $address */
            $address = $form->getData();
            $userAddressQuery = new UserAddressQuery($address);

            /** @var AddressCollection $addresses */
            $addresses = $this->queryBus->query($userAddressQuery);
            if ($addresses->isEmpty()) {
                $this->addFlashWarning('address.step1_action.no_address.warning');

                return $this->render('pages/account/address/step1.html.twig', compact('form'));
            }

            $userAddressStep1Data = new UserAddressStep1Data($address, $addresses);
            $this->saveStep1Data($session, $userAddressStep1Data);

            return $this->redirectToRoute(self::STEP2_ROUTE);
        }

        return $this->render('pages/account/address/step1.html.twig', compact('form'));
    }

    #[isGranted(User::ROLE_USER)]
    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-address/step-2',
        'fr' => MyAccountAction::BASE_URL_FR.'/mon-adresse/etape-2',
    ], name: self::STEP2_ROUTE)]
    public function step2(Request $request, SessionInterface $session): Response
    {
        // direct access is forbidden or empty addresses (should not happen)
        $userAddressStep1Data = $this->getStep1Data($session);
        if ($userAddressStep1Data === null || $userAddressStep1Data->addresses->isEmpty()) {
            return $this->redirectToRoute(self::STEP1_ROUTE);
        }

        // give the form the address choices to use
        $options = ['addresses' => $userAddressStep1Data->getAddressesAsArray()];
        $form = $this->createForm(AddressStep2FormType::class, null, $options)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var NominatimAddress $address */
            $address = $form->get('addresses')->getData(); // the selected address
            $command = new UpdateAddressCommand($this->getAppUser()->getId(), $userAddressStep1Data->address, $address);
            $this->commandBus->dispatch($command);
            $this->resetStep1Data($session);
            $this->addFlashSuccess('address.step2_action.form.success');

            return $this->redirectToRoute(MyAccountAction::ROUTE);
        }

        $parameters = $userAddressStep1Data->getData();
        $parameters['form'] = $form;

        return $this->render('pages/account/address/step2.html.twig', $parameters);
    }

    /**
     * Save data for step2 so we already have all we need to create the form and
     * save the data.
     */
    private function saveStep1Data(SessionInterface $session, UserAddressStep1Data $userAddressStep1Data): void
    {
        $session->set(self::STEP1_DATA_KEY, $userAddressStep1Data);
    }

    private function getStep1Data(SessionInterface $session): ?UserAddressStep1Data
    {
        /** @var ?UserAddressStep1Data $userAddressStep1Data */
        $userAddressStep1Data = $session->get(self::STEP1_DATA_KEY);

        return $userAddressStep1Data;
    }

    private function resetStep1Data(SessionInterface $session): void
    {
        $session->set(self::STEP1_DATA_KEY, null);
    }
}
