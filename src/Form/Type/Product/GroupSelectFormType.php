<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

final class GroupSelectFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        return $builder
            ->setMethod('GET')
            ->add('q', SearchType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'templates.pages.user.group.list.form.submit',
                'label_html' => true,
                'attr' => ['class' => 'search-input-button input-group-text'],
            ]);
    }
}
