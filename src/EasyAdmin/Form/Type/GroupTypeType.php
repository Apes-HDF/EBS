<?php

declare(strict_types=1);

namespace App\EasyAdmin\Form\Type;

use App\Controller\Admin\DashboardController;
use App\Enum\Group\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for the GroupType enumeration.
 */
class GroupTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => GroupType::getAsArray(),
            'translation_domain' => DashboardController::DOMAIN,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
