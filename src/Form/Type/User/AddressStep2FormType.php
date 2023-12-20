<?php

declare(strict_types=1);

namespace App\Form\Type\User;

use App\Controller\i18nTrait;
use Geocoder\Provider\Nominatim\Model\NominatimAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Allow to confirm an address from a given list.
 */
final class AddressStep2FormType extends AbstractType
{
    use i18nTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $i18nPrefix = $this->getI18nPrefix();
        /** @var array<NominatimAddress> $addresses */
        $addresses = $options['addresses'];

        $builder
            ->add('addresses', ChoiceType::class, [
                'label' => false,
                'label_attr' => [],
                'choices' => $addresses,
                'expanded' => true,
                'choice_label' => 'displayName',
                'constraints' => [
                    new NotNull(message: $i18nPrefix.'.addresses.not_null'),
                    new Choice(choices: $addresses),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $i18nPrefix.'.submit.label',
                'attr' => ['class' => 'btn-sm btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'addresses' => [],
        ]);
    }
}
