<?php

declare(strict_types=1);

namespace App\EasyAdmin\Form\Type;

use App\Controller\Admin\DashboardController;
use App\Enum\SocialMediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocialMediaTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => SocialMediaType::getAsArray(),
            'translation_domain' => DashboardController::DOMAIN,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
