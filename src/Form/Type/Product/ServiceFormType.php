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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ServiceFormType extends AbstractProductFormType
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
            ->add('duration', TextType::class, [
                'label' => 'new_service.form.serviceDuration',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'required' => false,
            ]);

        // only if the user is connected and has groups
        $groups = $this->groupRepository->getUserGroupsWithEnabledServices($user);
        if (!$user->getUserGroupsConfirmed()->isEmpty()) {
            $builder
                ->add('visibility', ChoiceType::class, [
                    'label' => 'product.form.visibility',
                    'expanded' => true,
                    'label_attr' => [
                        'class' => 'radio-inline text-black fs-6 fw-normal',
                    ],
                    'choices' => [
                        0 => [
                            'product.service.form.visibility' => ProductVisibility::RESTRICTED,
                        ],
                    ],
                    'multiple' => false,
                    'data' => ProductVisibility::RESTRICTED,
                    'disabled' => true,
                ])
                ->add('groups', EntityType::class, [
                    'class' => Group::class,
                    'query_builder' => $groups,
                    'label' => [] === $groups->getQuery()->getResult() ? $i18nPrefix.'.form.no_groups' : $i18nPrefix.'.form.groups',
                    'label_attr' => [] === $groups->getQuery()->getResult() ? ['class' => 'text-danger fs-6 fw-normal'] : [],
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                ]);
        }
        if ($groups->getQuery()->getResult() === []) {
            $builder->get('submit')->setDisabled(true);
        }
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
