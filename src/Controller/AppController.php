<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @see AppControllerTest
 */
final class AppController extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    #[Route(path: '/', name: 'root')]
    public function root(Request $request): Response
    {
        $locale = $request->getPreferredLanguage($this->getParameter('kernel.enabled_locales'));

        return $this->redirectToRoute('home', ['_locale' => $locale]);
    }

    #[Route(path: ['en' => '/en', 'fr' => '/fr'], name: 'home')]
    public function home(): Response
    {
        $page = $this->pageRepository->getHome();

        return $this->render('cms/page.html.twig', [
            'page' => $page,
            'is_home' => true,
        ]);
    }

    #[Route(path: '/ping', name: 'ping')]
    #[Route(path: '/healthz', name: 'healthz')]
    public function ping(): Response
    {
        return $this->json('OK');
    }
}
