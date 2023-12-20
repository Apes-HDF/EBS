<?php

declare(strict_types=1);

namespace App\Form\Type\Security;

use App\Message\Command\Security\ResetPasswordCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'account_create_action.password.invalid_message',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => [
                    'label' => 'account_create_action.password.first',
                    'help' => 'account_create_action.password.help',
                    'label_attr' => ['class' => 'text-black fw-light'],
                    'attr' => [
                        'class' => 'form-control-sm',
                        'data-password-visibility-target' => 'input',
                    ],
                    'required' => true,
                ],
                'second_options' => [
                    'label' => 'account_create_action.password.second',
                    'help' => 'account_create_action.password.help',
                    'label_attr' => ['class' => 'text-black fw-light'],
                    'attr' => [
                        'class' => 'form-control-sm',
                        'data-password-visibility-target' => 'input',
                    ],
                    'required' => true,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Réinitialiser mon mot de passe',
                'attr' => ['class' => 'btn btn-primary btn-sm'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResetPasswordCommand::class,
            'translation_domain' => 'security',
        ]);
    }
}
