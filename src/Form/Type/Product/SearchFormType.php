<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use App\Controller\i18nTrait;
use App\Dto\Product\Search;
use App\Entity\Address;
use App\Entity\Category;
use App\Entity\User;
use App\Geocoder\GeoProviderInterface;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\String\u;

final class SearchFormType extends AbstractType
{
    use i18nTrait;

    final public const DISTANCE_CHOICES = [
        1, 3, 5, 10, 15, 20,
    ];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly GeoProviderInterface $geoProvider,
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $distanceChoicesAttr = array_flip(self::DISTANCE_CHOICES);
        $distanceChoicesAttr = array_map(static fn () => ['class' => 'form-check-input border border-2 mx-auto'], $distanceChoicesAttr);
        $i18nPrefix = $this->getI18nPrefix();

        $builder
            ->add('q', TextType::class, [
                'empty_data' => '',
                'attr' => [
                    'placeholder' => $this->translator->trans($i18nPrefix.'.q.placeholder'),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'required' => false,
                'placeholder' => $this->translator->trans($i18nPrefix.'.category.placeholder'),
                'query_builder' => fn (CategoryRepository $er) => $er->getHierarchy(),
                'group_by' => fn (Category $category) => $this->translator->trans($i18nPrefix.'.'.$category->getType()->value),
                'choice_label' => 'getNameWithIndent',
            ])
            ->add('place', EntityType::class, [
                'required' => false,
                'placeholder' => $this->translator->trans($i18nPrefix.'.place.placeholder'),
                'class' => User::class,
                'query_builder' => fn (UserRepository $userRepository) => $userRepository->getPlacesQueryBuilder(),
                'group_by' => fn (User $user) => u($user->getAddress()?->getLocality())->lower()->title()->toString(),
                'choice_label' => fn (User $user) => $user->getDisplayName(),
            ])
            ->add('city', TextType::class)
            ->add('distance', ChoiceType::class, [
                'required' => false,
                'placeholder' => false,
                'expanded' => true,
                'choices' => array_combine(self::DISTANCE_CHOICES, self::DISTANCE_CHOICES),
                'choice_label' => fn (string $choice) => $choice.' km',
                'choice_attr' => $distanceChoicesAttr,
                'label' => $i18nPrefix.'.proximity.label',
            ])
            ->add('submit', SubmitType::class, [
                'label' => $i18nPrefix.'.submit.label',
                'attr' => ['class' => 'btn-sm btn-primary'],
            ]);

        $builder->get('city')
            ->addModelTransformer(new CallbackTransformer(
                function ($city): string {
                    return '';
                },
                function ($city): ?Address {
                    // transform the string back to an address
                    /** @var string $city */
                    if (u($city)->isEmpty()) {
                        return null;
                    }

                    return $this->geoProvider->getAddress($city);
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => [
                'novalidate' => 'novalidate',
                'data-search-target' => 'form',
            ],
            'data_class' => Search::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'p';
    }
}
