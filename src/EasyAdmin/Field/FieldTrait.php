<?php

declare(strict_types=1);

namespace App\EasyAdmin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

/**
 * Some fields helpers for EasyAdmin.
 */
trait FieldTrait
{
    /**
     * Render the boolean without switch: ✅ ❌.
     */
    public function getSimpleBooleanField(string $propertyName, ?string $label = null): BooleanField
    {
        return BooleanField::new($propertyName, $label)
            ->renderAsSwitch(false)
            ->setTemplatePath('easy_admin/field/boolean.html.twig');
    }

    /**
     * @return array<string, FormField>
     */
    public function getPanels(): array
    {
        return [
            'information' => FormField::addPanel('panel.information', 'fas fa-info-circle'),
            'tech_information' => FormField::addPanel('panel.tech_information', 'fas fa-history'),
            'payment_information' => FormField::addPanel('panel.payment_information', 'fas fa-dollar-sign'),
        ];
    }
}
