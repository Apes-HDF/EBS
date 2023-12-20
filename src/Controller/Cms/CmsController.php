<?php

declare(strict_types=1);

namespace App\Controller\Cms;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Simple CMS to display pages with rich content.
 *
 * @see CmsControllerTest
 */
#[Route(name: 'app_cms_')]
final class CmsController extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    /**
     * This is a route without i18n routes enabled.
     */
    #[Route(path: ['/{_locale}/{slug}'], name: 'page', priority: -1)]
    public function page(string $slug): Response
    {
        $page = $this->pageRepository->findOneBySlug($slug);
        if ($page === null || !$page->isEnabled()) {
            throw $this->createNotFoundException('Page not found.');
        }

        return $this->render('cms/page.html.twig', compact('page'));
    }
}
