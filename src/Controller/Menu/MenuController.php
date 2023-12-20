<?php

declare(strict_types=1);

namespace App\Controller\Menu;

use App\Entity\Configuration;
use App\Entity\Menu;
use App\Enum\Menu\LinkType;
use App\Repository\ConfigurationRepository;
use App\Repository\MenuItemRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class MenuController extends AbstractController
{
    public function __construct(
        private readonly MenuRepository $menuRepository,
        private readonly MenuItemRepository $menuItemRepository,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function menu(?string $q): Response
    {
        return $this->render(
            'components/layout/_navbar.html.twig',
            $this->menuItems($q)
        );
    }

    /**
     * @return array<mixed>
     *
     * @throws NonUniqueResultException
     */
    private function menuItems(?string $q): array
    {
        $menu = $this->menuRepository->getByCode(Menu::MENU);
        $firstItems = $this->menuItemRepository->findFirstLevelMenuLinks(Menu::MENU);
        $configuration = $this->configurationRepository->getInstanceConfiguration();
        Assert::isInstanceOf($configuration, Configuration::class);

        return [
            'menu' => $menu,
            'items' => $firstItems,
            'contactEnabled' => $configuration->getContactEnabled(),
            'contactEmail' => $configuration->getContactEmail(),
            'q' => $q,
        ];
    }

    /**
     * @throws NonUniqueResultException
     */
    public function footerItems(): Response
    {
        $menu = $this->menuRepository->getByCode(Menu::FOOTER);
        $links = $this->menuItemRepository->getFooterItems(LinkType::LINK->value);
        $icons = $this->menuItemRepository->getFooterItems(LinkType::SOCIAL_NETWORK->value);

        return $this->render(
            'components/layout/_footer.html.twig',
            [
                'menu' => $menu,
                'links' => $links,
                'icons' => $icons,
            ]
        );
    }
}
