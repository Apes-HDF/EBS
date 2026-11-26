<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use App\Controller\i18nTrait;
use App\Validator\Constraints\Date\BoundedDateRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateProductAvailabilityType extends AbstractType
{
    use i18nTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', DateType::class, [
                'label' => $this->getI18nPrefix().'.startAt',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => '',
                    'placeholder' => '',
                ],
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
            ])
            ->add('endAt', DateType::class, [
                'label' => $this->getI18nPrefix().'.endAt',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => '',
                    'placeholder' => '',
                ],
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->getI18nPrefix().'.submit',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [new BoundedDateRange()],
        ]);
    }
}
