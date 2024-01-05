<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Enum\User\UserType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * @see UserCrudControllerTest
 */
final class UserCrudController extends AbstractUserCrudController
{
    public function getUserType(): UserType
    {
        return UserType::USER;
    }

    public function getEntityLabelInPlural(): string
    {
        return 'menu.users';
    }

    public function getEntityLabelInSingular(): string
    {
        return 'USER';
    }

    public function configureFields(string $pageName): iterable
    {
        [
            'idField' => $idField,
            'emailField' => $emailField,
            'firstNameField' => $firstNameField,
            'lastNameField' => $lastNameField,
            'plainPassword' => $plainPassword,
            'enabledField' => $enabledField,
            'emailConfirmedField' => $emailConfirmedField,
            'loginAt' => $loginAt,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
            'avatarField' => $avatarField,
            'phoneNumberField' => $phoneNumberField,
            'categoryField' => $categoryField,
            'descriptionField' => $descriptionField,
            'smsNotificationsField' => $smsNotificationsField,
            'vacationModeField' => $vacationModeField,
            'addressField' => $addressField,
            'groupsCountField' => $groupsCountField,
        ] = $this->getFields($pageName);

        if ($pageName === Crud::PAGE_INDEX) {
            return [$emailField, $firstNameField, $lastNameField, $enabledField, $emailConfirmedField, $avatarField, $createdAt, $updatedAt, $loginAt, $groupsCountField];
        }

        $panels = $this->getPanels();

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            return [
                $panels['information'],
                $emailField,
                $firstNameField,
                $lastNameField,
                $avatarField,
                $phoneNumberField,
                $descriptionField,
                $categoryField,
                $smsNotificationsField,
                $vacationModeField,
                $plainPassword,

                $panels['tech_information'],
                $enabledField,
                $emailConfirmedField,
            ];
        }

        return [
            $panels['information'],
            $emailField,
            $firstNameField,
            $lastNameField,
            $avatarField,
            $phoneNumberField,
            $descriptionField,
            $addressField,
            $categoryField,
            $smsNotificationsField,
            $vacationModeField,

            $panels['tech_information'],
            $idField,
            $enabledField,
            $emailConfirmedField,
            $createdAt,
            $updatedAt,
            $loginAt,
        ];
    }
}
