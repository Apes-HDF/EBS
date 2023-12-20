<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use App\Entity\Category;
use App\Entity\User;
use App\Enum\Product\ProductType;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

abstract class AbstractProductCategorySelectFormType extends AbstractType
{
    abstract public function getProductType(): ProductType;

    public function __construct(
        public readonly Security $security,
        public readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $user = $this->security->getUser();
        Assert::isInstanceOf($user, User::class);

        return $builder
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => fn (CategoryRepository $er) => $er->getHierarchy($this->getProductType(), $user),
                'required' => false,
                'placeholder' => 'select_placeholder',
                'label' => false,
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
