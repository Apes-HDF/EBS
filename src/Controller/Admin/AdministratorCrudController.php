<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\User\UserType;
use App\Mailer\Email\Admin\New\NewAdminEmail;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * @see AdministratorCrudControllerTest
 */
final class AdministratorCrudController extends AbstractUserCrudController
{
    public function getUserType(): UserType
    {
        return UserType::ADMIN;
    }

    public function getEntityLabelInPlural(): string
    {
        return 'menu.administrators';
    }

    public function getEntityLabelInSingular(): string
    {
        return 'ADMIN';
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function createEntity(string $entityFqcn): User
    {
        $user = parent::createEntity($entityFqcn);
        $user->promoteToAdmin();

        return $user;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);

        $context = [];
        $context['user'] = $entityInstance;
        $this->mailer->send(NewAdminEmail::class, $context);
    }

    public function configureFields(string $pageName): iterable
    {
        $panels = $this->getPanels();

        [
            'idField' => $idField,
            'emailField' => $emailField,
            'firstNameField' => $firstNameField,
            'lastNameField' => $lastNameField,
            'plainPassword' => $plainPassword,
            'enabledField' => $enabledField,
            'loginAt' => $loginAt,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
        ] = $this->getFields($pageName);

        if ($pageName === Crud::PAGE_INDEX) {
            return [
                $emailField,
                $firstNameField,
                $lastNameField,
                $enabledField,
                $createdAt,
                $updatedAt,
                $loginAt,
            ];
        }

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            return [
                $emailField,
                $firstNameField,
                $lastNameField,
                $plainPassword,
                $enabledField,
            ];
        }

        // show
        return [
            $panels['information'],
            $emailField,
            $firstNameField,
            $lastNameField,
            $enabledField,

            $panels['tech_information'],
            $idField,
            $loginAt,
            $createdAt,
            $updatedAt,
        ];
    }
}
