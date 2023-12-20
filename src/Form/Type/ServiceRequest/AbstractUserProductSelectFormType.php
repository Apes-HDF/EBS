<?php

declare(strict_types=1);

namespace App\Form\Type\ServiceRequest;

use App\Entity\Product;
use App\Entity\ServiceRequest;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractUserProductSelectFormType extends AbstractType
{
    abstract public function isOwner(): bool;

    public function __construct(
        public readonly Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        return $builder
            ->setMethod('GET')
            ->add('product', EntityType::class, [
               'class' => Product::class,
                'query_builder' => function (EntityRepository $entityRepository) {
                    $qb = $entityRepository->createQueryBuilder('p')
                        ->from(ServiceRequest::class, 'sr')
                        ->andWhere('p = sr.product');

                    return $qb->andWhere(sprintf('sr.%s = :user', $this->isOwner() ? 'owner' : 'recipient'))
                        ->setParameter('user', $this->security->getUser());
                },
                'required' => false,
                'label' => false,
                'multiple' => true,
                'autocomplete' => true,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
