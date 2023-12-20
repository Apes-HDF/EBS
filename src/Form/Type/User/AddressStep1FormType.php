<?php

declare(strict_types=1);

namespace App\Form\Type\User;

use App\Entity\Address;
use App\Geocoder\GeoProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form to modify the main address of a user.
 */
final class AddressStep1FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class, [
                'label' => 'address.step1_action.form.address',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => 'form-control-sm',
                    'placeholder' => '',
                ],
            ])

            ->add('addressSupplement', TextType::class, [
                'label' => 'address.step1_action.form.address_supplement',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => 'form-control-sm',
                    'placeholder' => '',
                ],
                'required' => false,
            ])

            ->add('postalCode', TextType::class, [
                'label' => 'address.step1_action.form.postal_code',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => 'form-control-sm',
                    'placeholder' => '',
                ],
            ])

            ->add('locality', TextType::class, [
                'label' => 'address.step1_action.form.locality',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => 'form-control-sm',
                    'placeholder' => '',
                ],
            ])

            ->add('country', CountryType::class, [
                'label' => 'address.step1_action.form.country',
                'preferred_choices' => [GeoProviderInterface::DEFAULT_COUNTRY],
                'attr' => ['class' => 'form-control-sm'],
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'address.step1_action.form.submit',
                'attr' => ['class' => 'btn-sm btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
