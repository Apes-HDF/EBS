<?php

declare(strict_types=1);

namespace App\Form\Type\ServiceRequest;

use App\Entity\ServiceRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * New request service for a given product/service.
 */
final class CreateServiceRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => 'loan.new_action.form.message',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'height: 120px',
                    'placeholder' => '',
                ],
                'required' => false,
            ])
            ->add('startAt', DateType::class, [
                'label' => 'loan.new_action.form.startAt',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => '',
                    'placeholder' => '',
                ],
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => true, // turn this to false to apply a custom date picker
            ])
            ->add('endAt', DateType::class, [
                'label' => 'loan.new_action.form.endAt',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => '',
                    'placeholder' => '',
                ],
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => true, // turn this to false to apply a custom date picker
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'loan.new_action.form.submit',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceRequest::class,
            'validation_groups' => self::class,
        ]);
    }
}
