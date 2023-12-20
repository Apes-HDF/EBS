<?php

declare(strict_types=1);

namespace App\Form\Type\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordFormType extends AbstractType
{
    private const I18N_PREFIX = 'templates.pages.user.account.change_password';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'label' => self::I18N_PREFIX.'.old',
                'label_attr' => ['class' => 'fs-6 text-black'],
                'attr' => [
                    'class' => 'form-control form-control-sm',
                ],
                'required' => true,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => [
                    'label' => self::I18N_PREFIX.'.new',
                    'help' => self::I18N_PREFIX.'.help',
                    'label_attr' => ['class' => 'fs-6 text-black'],
                    'attr' => [
                        'class' => 'form-control form-control-sm',
                        'data-password-visibility-target' => 'input',
                    ],
                    'required' => true,
                ],
                'second_options' => [
                    'label' => self::I18N_PREFIX.'.confirm',
                    'help' => self::I18N_PREFIX.'.help',
                    'label_attr' => ['class' => 'fs-6 text-black'],
                    'attr' => [
                        'class' => 'form-control form-control-sm',
                        'data-password-visibility-target' => 'input',
                    ],
                    'required' => true,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => self::I18N_PREFIX.'.submit',
                'attr' => ['class' => 'btn btn-primary btn-sm'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => self::class,
        ]);
    }
}
