<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\FlashTrait;
use App\Form\Type\Security\LostPasswordFormType;
use App\Message\Command\Security\LostPasswordCommand;
use App\MessageBus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LostPasswordAction extends AbstractController
{
    use FlashTrait;

    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route(path: [
        'en' => '/{_locale}/account/lost-password',
        'fr' => '/{_locale}/compte/mot-de-passe-oublie',
    ], name: 'security_lost_password')]
    public function __invoke(Request $request, string $_route): Response
    {
        $form = $this->createForm(LostPasswordFormType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var LostPasswordCommand $lostPasswordCommand */
            $lostPasswordCommand = $form->getData();
            $this->commandBus->dispatch($lostPasswordCommand);
            $this->addFlashSuccess('lost_password.form.success');

            return $this->redirectToRoute($_route);
        }

        return $this->render('pages/password/lost.html.twig', compact('form'));
    }
}
