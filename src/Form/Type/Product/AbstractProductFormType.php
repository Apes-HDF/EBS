<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use App\Controller\i18nTrait;
use App\Entity\Category;
use App\Entity\Group;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\Product\ProductType;
use App\Enum\Product\ProductVisibility;
use App\Flysystem\MediaManager;
use App\Repository\CategoryRepository;
use App\Repository\GroupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractProductFormType extends AbstractType
{
    use i18nTrait;

    abstract public function getType(): ProductType;

    public function __construct(
        private readonly MediaManager $mediaManager,
        private readonly GroupRepository $groupRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $i18nPrefix = $this->getI18nPrefix();
        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => fn (CategoryRepository $er) => $er->getHierarchy($this->getType()),
                'label' => 'product.form.category',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'choice_label' => 'getNameWithIndent',
                'empty_data' => Category::getForEmptyData(), // trick to avoid type error when submitting a blank value in tests
            ])
            ->add('name', TextType::class, [
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'product.form.description',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'attr' => ['style' => 'height: 120px'],
            ])
            ->add('images', FileType::class, [
                'label' => 'product.form.images',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Count(
                        max: MediaManager::MAX_PHOTO_COUNT,
                        groups: [self::class]
                    ),
                    $this->mediaManager->getImageArrayConstraints(self::class),
                ],
                'help' => 'product.form.upload_infos',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'product.form.submit',
                'attr' => ['class' => 'btn-sm btn-primary'],
            ]);

        // only if the user has groups
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

        // remove all group associations if public to avoid side effects
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @var Product $product */
            $product = $event->getData();
            if ($product->getVisibility()->isPublic()) {
                $product->removeGroups();
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => self::class,
        ]);
    }
}
