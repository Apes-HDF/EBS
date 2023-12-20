<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\User;

use App\Entity\User;
use App\Enum\Product\ProductType;
use App\Message\Query\User\GetUserObjectsQuery;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
class GetUserObjectsQueryHandler
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(GetUserObjectsQuery $message): Query
    {
        $user = $this->userRepository->find($message->id);
        Assert::isInstanceOf($user, User::class);

        return $this->productRepository->getUserProductsByType($user, ProductType::OBJECT, $message->categoryId, null);
    }
}
