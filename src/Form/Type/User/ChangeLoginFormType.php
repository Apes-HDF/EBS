<?php

declare(strict_types=1);

namespace App\Form\Type\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangeLoginFormType extends AbstractType
{
    private const I18N_PREFIX = 'templates.pages.user.account.change_login';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'required' => true,
                'first_options' => [
                    'label' => self::I18N_PREFIX.'.form.email',
                    'label_attr' => ['class' => 'fs-6 text-black'],
                    'attr' => [
                        'class' => 'form-control form-control-sm',
                        'placeholder' => self::I18N_PREFIX.'.form.email_placeholder',
                    ],
                    'required' => true,
                ],
                'second_options' => [
                    'label' => self::I18N_PREFIX.'.form.email_repeat',
                    'label_attr' => ['class' => 'fs-6 text-black'],
                    'attr' => [
                        'class' => 'form-control form-control-sm',
                        'placeholder' => self::I18N_PREFIX.'.form.email_placeholder',
                    ],
                    'required' => true,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => self::I18N_PREFIX.'.form.submit',
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
