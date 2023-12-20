<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Type\Admin\ParametersFormType;
use App\Message\Command\Admin\ParametersFormCommand;
use App\Message\Query\Admin\ParametersFormQuery;
use App\MessageBus\CommandBus;
use App\MessageBus\QueryBus;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Route is protected at the security.yaml level.
 */
final class ParametersController extends AbstractController implements AdminSecuredCrudControllerInterface
{
    public const ROUTE_NAME = 'admin_parameters';

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route(path: '/admin/parameters', name: self::ROUTE_NAME)]
    public function __invoke(Request $request): Response
    {
        $parametersForm = $this->queryBus->query(new ParametersFormQuery());
        $form = $this->createForm(ParametersFormType::class, $parametersForm)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ParametersFormCommand $parametersForm */
            $parametersForm = $form->getData();
            $this->commandBus->dispatch($parametersForm);
            $this->addFlash(
                'success',
                'parameters_controller.form.success',
            );

            return $this->redirect($this->adminUrlGenerator->setRoute(self::ROUTE_NAME)->generateUrl());
        }

        return $this->render('admin/parameters.html.twig', compact('form'));
    }
}
