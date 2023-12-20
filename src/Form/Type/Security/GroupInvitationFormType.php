<?php

declare(strict_types=1);

namespace App\Form\Type\Security;

use App\Controller\i18nTrait;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form to send a group invitation to a given email.
 */
final class GroupInvitationFormType extends AbstractType
{
    use i18nTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->getI18nPrefix().'.email',
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->getI18nPrefix().'.submit',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'admin',
            'validation_groups' => self::class,
        ]);
    }
}
