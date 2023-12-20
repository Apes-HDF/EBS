<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User\Group;

use App\Doctrine\Manager\UserManager;
use App\Entity\Product;
use App\Message\Command\User\Group\QuitGroupCommand;
use App\Repository\GroupRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class QuitGroupCommandHandler
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly UserRepository $userRepository,
        private readonly ProductRepository $productRepository,
        private readonly UserManager $userManager,
    ) {
    }

    public function __invoke(QuitGroupCommand $message): void
    {
        $group = $this->groupRepository->get($message->groupId);
        $user = $this->userRepository->get($message->userId);
        $membership = $user->getGroupMembership($group);
        if ($membership === null) {
            throw new UnprocessableEntityHttpException('Membership not found.');
        }
        $user->removeUserGroup($membership);

        // get all products associated to this group
        /** @var array<Product> $products */
        $products = $this->productRepository->getUserProductsByType($user, null, null, $group)->execute();
        foreach ($products as $product) {
            $product->removeGroup($group);

            // this is a security: we must pause the object if the product is not
            // associated to other groups, because we don't want the product to
            // be public without the user consent. He will have to unpause the product
            // to make it searchable again by other users.
            if ($product->getGroups()->isEmpty()) {
                $product->setPublic();
                $product->setPaused();
            }

            // user choice for products (popup on quit group)
            if ($message->hasType()) {
                if ($message->isVacation()) {
                    $product->setPaused();
                } else {
                    $product->setPublic();
                    $product->setActive(); // here we can activate the product as it is the user choice
                }
            }

            $this->productRepository->save($product, true);
        }

        $this->userManager->save($user, true);
    }
}
