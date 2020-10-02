<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Entity\InventoryAvailability;
use App\Repository\ProductRepository;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\InventoryAvailabilityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="products_index")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     */
    public function index(ProductRepository $productRepo, InventoryRepository $inventoryRepo)
    {
        $products = $productRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        return $this->render('product/index_product.html.twig', [
            'products'    => $products,
            'inventories' => $inventories,
        ]);
    }

    /**
     * Permet de créer un Produit
     *
     * @Route("/product/new", name = "product_create")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager, InventoryRepository $inventoryRepo)
    { //
        $product = new Product();
        $product->setEnterprise($this->getUser()->getEnterprise());

        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        //  instancier un form externe
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        //dump($site);

        if ($form->isSubmitted() && $form->isValid()) {
            //Création de la disponibilité de stock pour le nouveau produit dans tous les inventaires(ou stock) existants
            //$inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
            foreach ($inventories as $inventory) {
                $available = 0;
                $inventoryAvailability = new InventoryAvailability();
                $inventoryAvailability->setAvailable($available);

                $inventory->addInventoryAvailability($inventoryAvailability);
                $product->addInventoryAvailability($inventoryAvailability);

                $manager->persist($inventoryAvailability);
                $manager->persist($inventory);
            }

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();

            $this->addFlash(
                'success',
                "The product <strong> {$product->getName()} </strong> has been registered successfully !"
            );

            return $this->redirectToRoute('products_index', [
                'id' => $product->getId(),
                //'inventories' => $inventories,
            ]);
        }


        return $this->render(
            'product/new.html.twig',
            [
                'form' => $form->createView(),
                'inventories' => $inventories,
            ]
        );
    }

    /**
     * Permet d'afficher le formulaire d'édition d'un product
     *
     * @Route("product/{id<\d+>}/edit", name="product_edit")
     * 
     * @Security("is_granted('ROLE_MANAGER')", message = "Vous n'avez pas le droit d'accéder à cette ressource")
     * 
     * @return Response
     */
    public function edit(Product $product, Request $request, InventoryRepository $inventoryRepo, EntityManagerInterface $manager)
    { //

        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //$product = $productRepo->findOneBy(['id' => $id]);
        //  instancier un form externe
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of the Product <strong> {$product->getName()} </strong> have been saved !"
            );

            return $this->redirectToRoute('products_index');
        }

        return $this->render('product/edit.html.twig', [
            'form'        => $form->createView(),
            'inventories' => $inventories,
        ]);
    }

    /**
     * Permet de supprimer un product
     * 
     * @Route("/product/{id}/delete", name="product_delete")
     *
     * @IsGranted("ROLE_MANAGER")
     * 
     * @param Product $product
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Product $product, EntityManagerInterface $manager, InventoryAvailabilityRepository $inventoryAvaiRepo)
    { // , 
        $inventoryAvailabilities = $inventoryAvaiRepo->findBy(['product' => $product]);
        //$inventories = $inventoryRepo->findAll();
        foreach ($inventoryAvailabilities as $inventoryAvailability) {
            $inventory = $inventoryAvailability->getInventory();
            if ($inventory->getEnterprise() == $this->getUser()->getEnterprise()) {
                $inventory->removeInventoryAvailability($inventoryAvailability);
                $manager->remove($inventoryAvailability);
                $manager->persist($inventory);
            }
        }
        $manager->remove($product);
        $manager->flush();

        $this->addFlash(
            'success',
            "The removal of Product <strong> {$product->getName()} </strong> has been completed successfully !"
        );

        return $this->redirectToRoute("products_index");
    }
}
