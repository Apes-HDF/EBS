<?php

declare(strict_types=1);

namespace App\Form\Type\User;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EditProfileFormType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {
    }

    private const I18N_PREFIX = 'templates.pages.user.account.edit_profile';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('avatar', FileType::class, [
                'label' => self::I18N_PREFIX.'.image',
                'mapped' => false,
                'required' => false,
                'help' => 'product.form.upload_infos',
            ])
            ->add('phone', PhoneNumberType::class, [
                'label' => self::I18N_PREFIX.'.phone',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'format' => PhoneNumberFormat::INTERNATIONAL,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'preferred_country_choices' => ['FR'],
                'required' => false,
            ])

            ->add('smsNotifications', CheckboxType::class, [
                'label' => self::I18N_PREFIX.'.sms',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => self::I18N_PREFIX.'.submit',
                'attr' => ['class' => 'btn-sm btn-primary'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => fn (CategoryRepository $er) => $er->getHierarchy(),
                'group_by' => fn (Category $category) => $this->translator->trans(self::I18N_PREFIX.'.'.$category->getType()->value),
                'label' => self::I18N_PREFIX.'.category',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'choice_label' => 'getNameWithIndent',
                'expanded' => false,
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => self::I18N_PREFIX.'.description',
                'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                'attr' => ['style' => 'height: 120px'],
                'required' => false,
            ]);

        if ($user->isPlace()) {
            $builder
                ->add('name', TextType::class, [
                    'label' => self::I18N_PREFIX.'.name',
                    'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                    'attr' => [
                        'class' => 'form-control-sm',
                    ],
                ])
                ->add('schedule', TextareaType::class, [
                    'label' => self::I18N_PREFIX.'.schedule',
                    'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                    'attr' => ['style' => 'height: 120px'],
                    'required' => false,
                ]);
        } else {
            $builder
                ->add('firstname', TextType::class, [
                    'label' => self::I18N_PREFIX.'.firstname',
                    'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                    'attr' => [
                        'class' => 'form-control-sm',
                    ],
                ])
                ->add('lastname', TextType::class, [
                    'label' => self::I18N_PREFIX.'.lastname',
                    'label_attr' => ['class' => 'text-black fs-6 fw-normal'],
                    'attr' => [
                        'class' => 'form-control-sm',
                    ],
                ]);
        }

        $builder
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                [$this, 'onPostSubmit']
            );
    }

    /**
     * Transforms the phone object to a string if validation is OK.
     *
     * @see AbstractUserCrudController::updateEntity
     */
    public function onPostSubmit(FormEvent $event): void
    {
        /** @var User $user */
        $user = $event->getData();
        $user->changePhoneNumber($user->phone);
        $event->setData($user);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => self::class,
        ]);
    }
}
