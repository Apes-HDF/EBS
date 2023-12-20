<?php

declare(strict_types=1);

namespace App\Controller\User\Product;

use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Doctrine\Manager\ProductManager;
use App\Entity\Product;
use App\Entity\User;
use App\Form\Type\Product\ObjectFormType;
use App\MessageBus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see ObjectControllerTest
 */
#[IsGranted(User::ROLE_USER)]
#[Route(name: 'app_object_')]
final class ObjectController extends AbstractController
{
    use SecurityTrait;
    use ProductTrait;

    public const REDIRECT_ROUTE = 'app_user_objects';

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly ProductManager $productManager,
    ) {
    }

    private function getForm(Product $product, Request $request): FormInterface
    {
        return $this->createForm(ObjectFormType::class, $product)->handleRequest($request);
    }

    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/new-object',
        'fr' => MyAccountAction::BASE_URL_FR.'/nouvel-objet',
    ], name: 'new')]
    public function new(Request $request, #[CurrentUser] User $user): Response
    {
        $product = $this->productManager->initObject($user);
        $form = $this->getForm($product, $request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array<UploadedFile>|null $images */
            $images = $form->get('images')->getData();
            $this->productManager->multipleUpload($images, $product);
            $this->productManager->save($product, true);

            return $this->redirectToRoute('app_product_show', $product->getRoutingParameters());
        }

        return $this->render('pages/product/new_object.html.twig', compact('form'));
    }

    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/objects/{id}/edit',
        'fr' => MyAccountAction::BASE_URL_FR.'/objets/{id}/editer',
    ],
        name: 'edit',
        requirements: ['slug' => Requirement::ASCII_SLUG, 'id' => Requirement::UUID_V6]
    )]
    public function edit(string $id, Request $request): Response
    {
        $product = $this->getProductForEdit($id);
        $form = $this->getForm($product, $request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array<UploadedFile>|null $images */
            $images = $form->get('images')->getData();
            $this->productManager->multipleUpload($images, $product);
            $this->productManager->save($product, true);

            return $this->redirectToRoute('app_product_show', $product->getRoutingParameters());
        }

        return $this->render('pages/product/edit_object.html.twig', compact('form', 'product'));
    }
}
