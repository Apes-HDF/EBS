<?php

declare(strict_types=1);

namespace App\Form\Type\User\ServiceRequest;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NewMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => 'new_message.form.message',
                'label_attr' => ['class' => ''],
                'attr' => [
                    'class' => '',
                    'placeholder' => 'new_message.form.message.placeholder',
                ],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'new_message.form.submit',
                'label_html' => true,
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'validation_groups' => self::class,
        ]);
    }
}
