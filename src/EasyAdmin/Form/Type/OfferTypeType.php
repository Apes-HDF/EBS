<?php

declare(strict_types=1);

namespace App\EasyAdmin\Form\Type;

use App\Controller\Admin\DashboardController;
use App\Enum\OfferType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for the OfferType enumeration.
 */
class OfferTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => OfferType::getAsArray(),
            'translation_domain' => DashboardController::DOMAIN,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
