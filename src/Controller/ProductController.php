<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Inventory;
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
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     */
    public function index(ProductRepository $productRepo)
    {
        $products = $productRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        return $this->render('product/index_product.html.twig', [
            'products'    => $products,

        ]);
    }

    /**
     * Permet de créer un Produit
     *
     * @Route("/product/new", name = "product_create")
     * 
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    { //
        $product = new Product();
        $product->setEnterprise($this->getUser()->getEnterprise())
            ->setType('Product');
        $categoryRepo = $manager->getRepository('App:Category');
        $categories_ = $categoryRepo->findBy(['entreprise' => $this->getUser()->getEnterprise()]);
        $categories = [];
        foreach ($categories_ as $category) {
            $categories[$category->getName()] = $category->getName();
        }
        $inventoryRepo = $manager->getRepository('App:Inventory');
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        //  instancier un form externe
        $form = $this->createForm(ProductType::class, $product, [
            'entId' => $this->getUser()->getEnterprise()->getId(),
            'categories' => $categories,
        ]);
        $form->handleRequest($request);
        //dump($site);
        $valid = true;
        if ($form->isSubmitted() && $form->isValid()) {
            if ($product->getHasStock() == true) {
                //Vérification de l'abonnement pour autoriser l'enregistrement de la référence en stock
                $iconStock = true;
                //$iconCombination = true;
                $productRefNumber = $this->getUser()->getEnterprise()->getSubscription()->getProductRefNumber();
                $sheetNumber = $this->getUser()->getEnterprise()->getSubscription()->getSheetNumber();
                if ($productRefNumber == 0) { //Si le nombre de référence est 0 alors subscription au module stock désactiver
                    $iconStock = false;
                    $valid = false;
                }
                if ($iconStock) {
                    $productRepo = $manager->getRepository('App:Product');
                    $nbProductsInStock = count($productRepo->findBy(['hasStock' => 1]));

                    //Si le nombre de produit déjà crée en stock est inférieur au nbre de ref autorisé par l'abonnement
                    if (($nbProductsInStock < $productRefNumber) || ($productRefNumber == 19022020)) {
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
                    } else {
                        $valid = false;
                    }
                }
            }

            //$manager = $this->getDoctrine()->getManager();
            if ($valid == true) {

                $categoryRepo = $manager->getRepository('App:Category');
                foreach ($product->getCategories() as $category) {
                    //dump($category);
                    $category->setName($category->getCategory()->getName())
                        ->setEntreprise($this->getUser()->getEnterprise());
                    //Je vérifie si la catégorie est déjà existante en BDD pour éviter les doublons 
                    $category_ = $categoryRepo->findOneBy([
                        'name' => $category->getName(),
                        'entreprise' => $this->getUser()->getEnterprise()
                    ]);

                    if (empty($category_)) {
                        $category->addProduct($product);
                        $manager->persist($category);
                        // dump('category dont exists ');
                    } else {
                        //dump('category exists with id = ' . $category_->getId());
                        //$category = $category_;
                        $category_->addProduct($product);
                        $product->removeCategory($category);
                        $product->addCategory($category_);
                    }
                }
                $manager->persist($product);
                $manager->flush();
                $this->addFlash(
                    'success',
                    "The product <strong> {$product->getName()} </strong> has been registered successfully !"
                );
            } else {
                $this->addFlash(
                    'success',
                    "Désolé le produit <strong> {$product->getName()} </strong> n'a pas été enregistré car vous avez déjà atteint votre limite de réference en stock !"
                );
            }


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
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_STOCK_MANAGER') and product.getEnterprise() === user.getEnterprise() )" )
     * 
     * @return Response
     */
    public function edit(Product $product, Request $request, EntityManagerInterface $manager)
    { //

        $categoryRepo = $manager->getRepository('App:Category');
        $categories_ = $categoryRepo->findBy(['entreprise' => $this->getUser()->getEnterprise()]);
        $categories = [];
        foreach ($categories_ as $category) {
            $categories[$category->getName()] = $category->getName();
        }
        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        //  instancier un form externe
        $form = $this->createForm(ProductType::class, $product, [
            'entId' => $this->getUser()->getEnterprise()->getId(),
            'categories' => $categories,
        ]); //  instancier un form externe
        $form = $this->createForm(ProductType::class, $product, [
            'entId' => $this->getUser()->getEnterprise()->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryRepo = $manager->getRepository('App:Category');
            foreach ($product->getCategories() as $category) {
                //dump($category);
                $category->setName($category->getCategory()->getName())
                    ->setEntreprise($this->getUser()->getEnterprise());
                //Je vérifie si la catégorie est déjà existante en BDD pour éviter les doublons 
                $category_ = $categoryRepo->findOneBy([
                    'name' => $category->getName(),
                    'entreprise' => $this->getUser()->getEnterprise()
                ]);

                if (empty($category_)) {
                    $category->addProduct($product);
                    $manager->persist($category);
                    // dump('category dont exists ');
                } else {
                    //dump('category exists with id = ' . $category_->getId());
                    //$category = $category_;
                    $category_->addProduct($product);
                    $product->removeCategory($category);
                    $product->addCategory($category_);
                }
            }
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

        ]);
    }

    /**
     * Permet de supprimer un product
     * 
     * @Route("/product/{id}/delete", name="product_delete")
     *
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_STOCK_MANAGER') and product.getEnterprise() === user.getEnterprise() )" )
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
