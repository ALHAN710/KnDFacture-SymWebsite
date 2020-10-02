<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Form\InventoryType;
use App\Entity\InventoryAvailability;
use App\Repository\ProductRepository;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InventoryController extends AbstractController
{
    /**
     * @Route("/inventory/dashboard", name="inventories_index")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     */
    public function index(InventoryRepository $inventoryRepo)
    {
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        return $this->render('inventory/index_inventory.html.twig', [
            'inventories' => $inventories,
        ]);
    }

    /**
     * @Route("/inventory/{id<\d+>}/dashboard", name="inventory_index")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     */
    public function inventoryDash(Inventory $inventory, EntityManagerInterface $manager, InventoryRepository $inventoryRepo)
    {
        /*$dataGrid = $manager->createQuery("SELECT SUM(d.kWh) AS EA, SUM(d.kVarh) AS ER, MAX(d.dateTime) AS DateTimeMax,
                                            MAX(d.s3ph) AS Smax, AVG(d.s3ph) AS Smoy, SUM(d.kWh) / SQRT( (SUM(d.kWh)*SUM(d.kWh)) + (SUM(d.kVarh)*SUM(d.kVarh)) ) AS Cosphi
                                            FROM App\Entity\Order d
                                            JOIN d.smartMod sm 
                                            WHERE d.dateTime LIKE :selDate
                                            AND sm.id = :modId
                                                                                                                                
                                            ")
            ->setParameters(array(
                //'selDate' => $dat,
                //'modId'   => $gridId
            ))
            ->getResult();*/

        /*$Energy = $manager->createQuery("SELECT SUBSTRING(d.dateTime, 1, 10) AS jour, SUM(d.kWh) AS kWh, SUM(d.kVarh) AS kVarh 
                                        FROM App\Entity\DataMod d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :selDate
                                        AND sm.id = :smartModId
                                        GROUP BY jour
                                        ORDER BY jour ASC
                                                                                
                                        ")
            ->setParameters(array(
                //'selDate'      => $dat,
                //'smartModId'   => $id
            ))
            ->getResult();*/

        $inventories = $inventoryRepo->findAll();

        return $this->render('inventory/index_inventory.html.twig', [
            'inventories' => $inventories,
        ]);
    }

    /**
     * Permet de créer un Inventory
     *
     * @Route("/inventory/new", name = "inventory_create")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     * @return Response
     */
    public function create(Request $request, InventoryRepository $inventoryRepo, EntityManagerInterface $manager, ProductRepository $productRepo)
    { //
        $inventory = new Inventory();
        $inventory->setEnterprise($this->getUser()->getEnterprise());
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        //  instancier un form externe
        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);
        //dump($site);

        if ($form->isSubmitted() && $form->isValid()) {
            //Création de la disponibilité de stock pour le nouvel inventaire de tous les produits existants
            $products = $productRepo->findBy(['enterprise' => $inventory->getEnterprise()]);
            $available = 0;
            $inventoryAvailability = new InventoryAvailability();
            foreach ($products as $product) {
                $inventoryAvailability->setAvailable($available)
                    ->setInventory($inventory)
                    ->setProduct($product);

                $manager->persist($inventoryAvailability);
            }

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($inventory);
            $manager->flush();

            $this->addFlash(
                'success',
                "The Inventory <strong> {$inventory->getName()} </strong> has been registered successfully !"
            );

            return $this->redirectToRoute('inventories_index');
        }


        return $this->render(
            'inventory/new.html.twig',
            [
                'form'        => $form->createView(),
                'inventories' => $inventories,
            ]
        );
    }

    /**
     * Permet d'afficher le formulaire d'édition d'un inventory
     *
     * @Route("inventory/{id<\d+>}/edit", name="inventory_edit")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     * @return Response
     */
    public function edit(Inventory $inventory, Request $request, InventoryRepository $inventoryRepo, EntityManagerInterface $manager)
    { //@Security("is_granted('ROLE_MANAGER')", message = "Vous n'avez pas le droit d'accéder à cette ressource")

        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //$product = $productRepo->findOneBy(['id' => $id]);
        //  instancier un form externe
        $form = $this->createForm(InventoryType::class, $inventory);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($inventory);
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of the Inventory <strong> {$inventory->getName()} </strong> have been saved !"
            );

            return $this->redirectToRoute('inventories_index');
        }

        return $this->render('inventory/edit.html.twig', [
            'form'         => $form->createView(),
            'inventories'  => $inventories,
            'txServiceVal' => $inventory->getTxOfService()
        ]);
    }

    /**
     * Permet de supprimer un inventaire
     * 
     * @Route("/inventory/{id}/delete", name="inventory_delete")
     *
     * @IsGranted("ROLE_MANAGER")
     * 
     * @param Inventory $inventory
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Inventory $inventory, EntityManagerInterface $manager)
    {
        $manager->remove($inventory);
        $manager->flush();

        $this->addFlash(
            'success',
            "The removal of Inventory <strong> {$inventory->getName()} </strong> has been completed successfully !"
        );

        return $this->redirectToRoute("inventories_index");
    }
}
