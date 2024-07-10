<?php

declare(strict_types=1);

namespace App\Form\Type\Group;

use App\Entity\Configuration;
use App\Entity\Group;
use App\Entity\User;
use App\Enum\Group\GroupMembership;
use App\Enum\Group\GroupType;
use App\Repository\ConfigurationRepository;
use App\Repository\GroupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

class CreateGroupFormType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly GroupRepository $groupRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $configuration = $this->configurationRepository->getInstanceConfiguration();
        $myGroupsWithDisabledServices = $this->groupRepository->getGroupsByEnabledServices(false, $user);

        $builder
            ->add('name', TextType::class, [
                'label' => 'templates.pages.group.create.form.name',
                'label_attr' => ['class' => 'fs-6 text-black'],
                'attr' => [
                    'class' => 'form-control-sm',
                ],
            ])
            ->add('type', EnumType::class, [
                'class' => GroupType::class,
                'label' => 'templates.pages.group.create.form.type',
                'label_attr' => ['class' => 'fs-6 text-black'],
                'expanded' => true,
                'choice_label' => 'transKey',
            ]);
        Assert::isInstanceOf($configuration, Configuration::class);
        if ($configuration->getServicesEnabled()) {
            $builder
                ->add('servicesEnabled', CheckboxType::class, [
                    'label' => 'templates.pages.group.create.form.servicesEnabled',
                    'label_attr' => ['class' => 'fs-6 text-black mb-3 switch-custom'],
                    'required' => false,
                    'attr' => [
                        'data-action' => 'click->parentgroup#updateParentOptions',
                        'data-parentgroup-target' => 'servicesEnabledField',
                        'data-user-id' => $user->getId(),
                    ],
                ]);
        }
        $builder
        ->add('membership', EnumType::class, [
            'class' => GroupMembership::class,
            'label' => 'templates.pages.group.create.form.membership',
            'label_attr' => ['class' => 'fs-6 text-black'],
            'expanded' => true,
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'templates.pages.group.create.form.submit',
            'attr' => ['class' => 'btn btn-primary btn-sm d-grid col-12 my-5'],
        ]);

        if ([] === $myGroupsWithDisabledServices) {
            $builder
                ->add('parent', EntityType::class, [
                    'class' => Group::class,
                    'choices' => $myGroupsWithDisabledServices,
                    'label' => 'templates.pages.group.create.form.subgroup',
                    'label_attr' => ['class' => 'fs-6 text-black'],
                    'required' => false,
                    'attr' => [
                        'data-parentgroup-target' => 'parentField',
                    ],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
            'validation_groups' => self::class,
        ]);
    }
}
