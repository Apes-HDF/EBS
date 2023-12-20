<?php

declare(strict_types=1);

namespace App\Form\Type\Group;

use App\Entity\Group;
use App\Entity\User;
use App\Enum\Group\GroupMembership;
use App\Enum\Group\GroupType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateGroupFormType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

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
            ])
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

        $myGroups = $user->getMyGroupsAsAdmin();
        if (!$myGroups->isEmpty()) {
            $builder
                ->add('parent', EntityType::class, [
                    'class' => Group::class,
                    'choices' => $myGroups,
                    'label' => 'templates.pages.group.create.form.subgroup',
                    'label_attr' => ['class' => 'fs-6 text-black'],
                    'required' => false,
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
