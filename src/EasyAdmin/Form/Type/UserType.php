<?php

declare(strict_types=1);

namespace App\EasyAdmin\Form\Type;

use App\Controller\Admin\DashboardController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Checker\AuthorizationChecker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for filtering users on selected groups.
 */
class UserType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly AuthorizationChecker $authorizationChecker,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => User::class,
            'className' => User::class,
            'translation_domain' => DashboardController::DOMAIN,
        ]);

        // restrict to allowed groups only
        if (!$this->authorizationChecker->isAdmin()) {
            /** @var User $user */
            $user = $this->security->getUser();

            $resolver->setDefault('query_builder', function (UserRepository $repo) use ($user) {
                return $repo->createQueryBuilder('entity')
                    ->innerJoin('entity.userGroups', 'ug')
                    ->andWhere('ug.group IN (:groups)')
                    ->setParameter(':groups', $user->getMyGroupsAsAdmin());
            });
        }
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
