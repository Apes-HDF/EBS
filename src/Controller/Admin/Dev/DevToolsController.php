<?php

declare(strict_types=1);

namespace App\Controller\Admin\Dev;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;

final class DevToolsController extends AbstractController
{
    public const ROUTE_NAME = 'admin_dev_tools';

    #[Route(path: '/admin/dev/tools', name: self::ROUTE_NAME)]
    public function __invoke(Request $request): Response
    {
        $transCodes = [];
        for ($x = 1; $x <= 10; ++$x) {
            $transCodes[] = ByteString::fromRandom(7);
        }

        $uuidV4 = Uuid::v4();
        $uuidV6 = Uuid::v6();
        $encoded = '+';

        $urlEncoded = urlencode($encoded);
        $confirmationCode1 = bin2hex(random_bytes(25));
        $confirmationCode2 = ByteString::fromRandom(50);

        return $this->render('admin/dev/dev_tools.html.twig', compact(
            'transCodes',
            'uuidV4',
            'uuidV6',
            'encoded',
            'urlEncoded',
            'confirmationCode1',
            'confirmationCode2',
        ));
    }
}
