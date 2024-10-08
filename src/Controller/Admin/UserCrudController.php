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
            'membershipPaidField' => $membershipPaidField,
            'startAt' => $startAt,
            'endAt' => $endAt,
            'expiresInField' => $expiresInField,
            'payedAt' => $payedAt,
            'offerField' => $offerField,
        ] = $this->getFields($pageName);

        if ($pageName === Crud::PAGE_INDEX) {
            $listFields = [$emailField, $firstNameField, $lastNameField, $enabledField, $emailConfirmedField, $avatarField, $createdAt, $updatedAt, $loginAt, $groupsCountField];
            if ($this->platformRequiresGlobalPayment()) {
                $listFields[] = $membershipPaidField;
            }

            return $listFields;
        }

        $panels = $this->getPanels();

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $editFields = [
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
            if ($this->platformRequiresGlobalPayment()) {
                $editFields = array_merge($editFields, [
                    $panels['payment_information'],
                    $membershipPaidField,
                    $offerField,
                    $startAt,
                    $endAt,
                    $payedAt,
                ]);
            }

            return $editFields;
        }

        $showFields = [
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
        if ($this->platformRequiresGlobalPayment()) {
            $showFields = array_merge($showFields, [
                $panels['payment_information'],
                $membershipPaidField,
                $startAt,
                $endAt,
                $payedAt,
                $expiresInField,
            ]);
        }

        return $showFields;
    }
}
