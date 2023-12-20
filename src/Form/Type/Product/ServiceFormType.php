<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use App\Entity\Product;
use App\Enum\Product\ProductType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ServiceFormType extends AbstractProductFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('duration', TextType::class, [
                'label' => 'new_service.form.serviceDuration',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'required' => false,
            ]);
    }

    public function getType(): ProductType
    {
        return ProductType::SERVICE;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Product::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
