<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use App\Controller\Admin\DashboardController;
use App\Message\Command\Admin\ParametersFormCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Admin form for the instance parameters.
 *
 * @see ParametersForm
 */
final class ParametersFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('servicesEnabled', CheckboxType::class, [
                'label' => 'parameter.services',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
            ])

            ->add('notificationsSenderEmail', EmailType::class, [
                'label' => 'parameter.mail',
                'label_attr' => ['class' => 'col-sm-2 col-form-label'],
            ])

            ->add('notificationsSenderName', TextType::class, [
                'label' => 'parameter.name',
            ])

            ->add('contactFormEnabled', CheckboxType::class, [
                'label' => 'parameter.formVisibility',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
            ])
            ->add('contactFormEmail', EmailType::class, [
                'label' => 'parameter.receptionEmail',
            ])

            ->add('groupsEnabled', CheckboxType::class, [
                'label' => 'parameter.groups',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
            ])

            ->add('groupsCreationMode', ChoiceType::class, [
                'label' => 'parameter.groupsCreation',
                'expanded' => true,
                'choices' => [
                    'parameter.groupsCreationForAll' => ParametersFormCommand::ALL,
                    'parameter.groupsCreationOnlyForAdmin' => ParametersFormCommand::ONLY_ADMIN,
                ],
                'label_attr' => [
                    'class' => 'radio-inline',
                ],
            ])

            ->add('groupsPaying', CheckboxType::class, [
                'label' => 'parameter.paidGroupsCreation',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
            ])

            ->add('confidentialityConversationAdminAccess', CheckboxType::class, [
                'label' => 'parameter.conversationsVisibility',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'parameter.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'data_class' => ParametersFormCommand::class,
            'attr' => [
                'novalidate' => 'novalidate', // test constraintes HTML
            ],
            'translation_domain' => DashboardController::DOMAIN,
        ]);
    }
}
