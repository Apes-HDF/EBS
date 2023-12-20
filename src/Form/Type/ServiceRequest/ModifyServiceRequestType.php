<?php

declare(strict_types=1);

namespace App\Form\Type\ServiceRequest;

use App\Entity\ServiceRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form allowing the users to modify the dates of the service request.
 */
final class ModifyServiceRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', DateType::class, [
                'label' => 'loan.new_action.form.startAt',
                'label_attr' => ['class' => ''],
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('endAt', DateType::class, [
                'label' => 'loan.new_action.form.endAt',
                'label_attr' => ['class' => ''],
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceRequest::class,
            'validation_groups' => CreateServiceRequestType::class,
        ]);
    }
}
