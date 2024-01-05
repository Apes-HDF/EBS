<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Enum\User\UserType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

final class PlaceCrudController extends AbstractUserCrudController
{
    public function getUserType(): UserType
    {
        return UserType::PLACE;
    }

    public function getEntityLabelInPlural(): string
    {
        return 'menu.places';
    }

    public function getEntityLabelInSingular(): string
    {
        return 'PLACE';
    }

    public function configureFields(string $pageName): iterable
    {
        $panels = $this->getPanels();

        [
            'idField' => $idField,
            'emailField' => $emailField,
            'nameField' => $nameField,
            'phoneNumberField' => $phoneNumberField,
            'avatarField' => $avatarField,
            'descriptionField' => $descriptionField,
            'categoryField' => $categoryField,
            'smsNotificationsField' => $smsNotificationsField,
            'vacationModeField' => $vacationModeField,
            'scheduleField' => $scheduleField,
            'plainPassword' => $plainPassword,
            'enabledField' => $enabledField,
            'emailConfirmedField' => $emailConfirmedField,
            'loginAt' => $loginAt,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
            'groupsCountField' => $groupsCountField,
        ] = $this->getFields($pageName);

        if ($pageName === Crud::PAGE_INDEX) {
            return [$emailField, $nameField, $enabledField, $emailConfirmedField, $createdAt, $updatedAt, $loginAt, $groupsCountField];
        }

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            return [$panels['information'], $emailField, $nameField, $phoneNumberField, $avatarField, $descriptionField, $categoryField, $smsNotificationsField, $vacationModeField, $scheduleField, $plainPassword, $panels['tech_information'], $enabledField, $emailConfirmedField];
        }

        // show

        return [$panels['information'], $emailField, $nameField, $phoneNumberField, $avatarField, $descriptionField, $scheduleField, $enabledField, $panels['tech_information'], $emailConfirmedField, $idField, $createdAt, $updatedAt, $loginAt];
    }
}
