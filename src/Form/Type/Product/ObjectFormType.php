<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use App\Entity\Group;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\Product\ProductType;
use App\Enum\Product\ProductVisibility;
use App\Flysystem\MediaManager;
use App\Repository\GroupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ObjectFormType extends AbstractProductFormType
{
    public function __construct(
        MediaManager $mediaManager,
        private readonly GroupRepository $groupRepository,
        private readonly Security $security,
    ) {
        parent::__construct($mediaManager, $security);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $i18nPrefix = $this->getI18nPrefix();
        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('age', TextType::class, [
                'label' => 'object.form.age',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'required' => false,
            ])
            ->add('deposit', MoneyType::class, [
                'label' => 'object.form.deposit',
                'divisor' => 100,
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'required' => false,
            ])
            ->add('preferredLoanDuration', TextType::class, [
                'label' => 'object.form.preferredLoanDuration',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'required' => false,
            ]);

        // only if the user is connected and has groups
        if (!$user->getUserGroupsConfirmed()->isEmpty()) {
            $builder
                ->add('visibility', EnumType::class, [
                    'class' => ProductVisibility::class,
                    'label' => 'product.form.visibility',
                    'expanded' => true,
                    'label_attr' => [
                        'class' => 'radio-inline text-black fs-6 fw-normal',
                    ],
                    // check if we can do simpler (@see productvisibility_controller.js)
                    'choice_attr' => [
                        0 => [
                            'data-productvisibility-target' => ProductVisibility::PUBLIC->value,
                            'data-action' => 'click->productvisibility#hideGroups',
                        ],
                        1 => [
                            'data-productvisibility-target' => ProductVisibility::RESTRICTED->value,
                            'data-action' => 'click->productvisibility#showGroups',
                        ],
                    ],
                ])
                ->add('groups', EntityType::class, [
                    'class' => Group::class,
                    'query_builder' => $this->groupRepository->getUserGroups($user),
                    'label' => $i18nPrefix.'.form.groups',
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                ]);
        }
    }

    public function getType(): ProductType
    {
        return ProductType::OBJECT;
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
