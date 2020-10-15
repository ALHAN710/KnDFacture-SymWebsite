<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="index_categories")
     */
    public function index(CategoryRepository $categoryRepo)
    {
        $categories = $categoryRepo->findBy(['entreprise' => $this->getUser()->getEnterprise()]);
        return $this->render('category/index_categories.html.twig', [
            'categories'    => $categories,
        ]);
    }

    /**
     * Permet de créer un Produit
     *
     * @Route("/category/new", name = "category_create")
     * 
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    { //
        $category = new Category();
        $category->setEntreprise($this->getUser()->getEnterprise());

        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        //  instancier un form externe
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();

            $manager->persist($category);
            $manager->flush();
            $this->addFlash(
                'success',
                "La catégorie <strong> {$category->getName()} </strong> a été enregistrée avec succès !"
            );

            return $this->redirectToRoute('index_categories', [
                'id' => $category->getId(),
                //'inventories' => $inventories,
            ]);
        }


        return $this->render(
            'category/new.html.twig',
            [
                'form' => $form->createView(),
                //'inventories' => $inventories,
            ]
        );
    }

    /**
     * Permet d'afficher le formulaire d'édition d'un product
     *
     * @Route("category/{id<\d+>}/edit", name="category_edit")
     * 
     * @Security("is_granted('ROLE_STOCK_MANAGER')", message = "Vous n'avez pas le droit d'accéder à cette ressource")
     * 
     * @return Response
     */
    public function edit(Category $category, Request $request, EntityManagerInterface $manager)
    { //

        //  instancier un form externe
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($category);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications de la catégorie <strong> {$category->getName()} </strong> ont bien enregistrées !"
            );

            return $this->redirectToRoute('index_categories');
        }

        return $this->render('category/edit.html.twig', [
            'form'        => $form->createView(),

        ]);
    }

    /**
     * Permet de supprimer une catégorie 
     * 
     * @Route("/category/{id}/delete", name="category_delete")
     *
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     * @param Category $category
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Category $category, EntityManagerInterface $manager)
    {
        $manager->remove($category);
        $manager->flush();

        $this->addFlash(
            'success',
            "La catégorie <strong> {$category->getName()} </strong> a été supprimé avec succès !"
        );

        return $this->redirectToRoute("index_categories");
    }
}
