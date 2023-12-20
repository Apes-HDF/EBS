<?php

declare(strict_types=1);

namespace App\Form\Type\Security;

use App\Entity\User;
use App\Enum\User\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Admin form for the instance parameters.
 *
 * @see AccountCreateController
 */
final class AccountCreateStep2FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'account_create_action.account_type',
                'label_attr' => ['class' => 'text-black fw-light'],
                'choices' => UserType::getForFront(),
                'choice_attr' => function () {
                    return [
                        'data-controller' => 'account',
                        'data-action' => 'click->account#choosenType',
                    ];
                },
                'expanded' => true,
            ])

            ->add('firstname', TextType::class, [
                'label' => 'account_create_action.firsname',
                'label_attr' => ['class' => 'text-black fw-light required'],
                'attr' => [
                    'class' => 'form-control-sm input-firstname',
                    'placeholder' => 'account_create_action.firsname.placeholder',
                ],
                'required' => false,
            ])

            ->add('lastname', TextType::class, [
                'label' => 'account_create_action.lastname',
                'label_attr' => ['class' => 'text-black fw-light required'],
                'attr' => [
                    'class' => 'form-control-sm input-lastname',
                    'placeholder' => 'account_create_action.lastname.placeholder',
                ],
                'required' => false,
            ])

            ->add('name', TextType::class, [
                'label' => 'account_create_action.name',
                'label_attr' => ['class' => 'text-black fw-light required'],
                'attr' => [
                    'class' => 'form-control-sm input-name',
                    'placeholder' => 'account_create_action.name.placeholder',
                ],
                'required' => false,
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'account_create_action.password.invalid_message',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => [
                    'label' => 'account_create_action.password.first',
                    'help' => 'account_create_action.password.help',
                    'label_attr' => ['class' => 'text-black fw-light'],
                    'attr' => [
                        'class' => 'form-control-sm',
                        'placeholder' => '',
                        'data-password-visibility-target' => 'input',
                        'spellcheck' => false,
                    ],
                    'required' => true,
                ],
                'second_options' => [
                    'label' => 'account_create_action.password.second',
                    'help' => 'account_create_action.password.help',
                    'label_attr' => ['class' => 'text-black fw-light'],
                    'attr' => [
                        'class' => 'form-control-sm',
                        'placeholder' => '',
                        'data-password-visibility-target' => 'input',
                        'spellcheck' => false,
                    ],
                    'required' => true,
                ],
            ])

            ->add('gdpr', CheckboxType::class, [
                'label' => 'account_create_action.gdpr',
                'label_translation_parameters' => [
                    '%link%' => '/fr/cgu',
                ],
                'label_html' => true,
                'label_attr' => ['class' => 'fw-light text-black gdpr'],
                'required' => true,
                'data' => false,
                'validation_groups' => [self::class],
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'account_create_action.title',
                'attr' => ['class' => 'btn btn-primary btn-sm'],
            ])
        ;

        $builder->get('type')->addModelTransformer(new CallbackTransformer(
            function (?UserType $enumToString) {
                return $enumToString === null ? '' : $enumToString->value;
            },
            function (string $stringToEnum) {
                return UserType::from($stringToEnum);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'security',
            'validation_groups' => ['Default', self::class],
        ]);
    }
}
