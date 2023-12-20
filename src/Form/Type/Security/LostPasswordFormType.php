<?php

declare(strict_types=1);

namespace App\Form\Type\Security;

use App\Message\Command\Security\LostPasswordCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LostPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'label_attr' => ['class' => 'text-black fw-light'],
                'attr' => [
                    'class' => 'form-control-sm',
                    'placeholder' => 'lost_password.form.email.placeholder',
                ],
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'lost_password.form.submit',
                'attr' => ['class' => 'btn btn-primary btn-sm'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LostPasswordCommand::class,
            'translation_domain' => 'security',
        ]);
    }
}
