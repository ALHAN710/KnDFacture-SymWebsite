<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Lot;
use App\Form\LotType;
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

class InventoryController extends ApplicationController
{
    /**
     * @Route("/inventory/dashboard", name="inventories_index")
     * 
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     */
    public function index(InventoryRepository $inventoryRepo)
    {
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        return $this->render('inventory/index_inventories.html.twig', [
            'inventories' => $inventories,
        ]);
    }

    /**
     * Permet de créer un Inventory
     *
     * @Route("/inventory/new", name = "inventory_create")
     * 
     * @IsGranted("ROLE_STOCK_MANAGER")
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
            $products = $productRepo->findBy(['enterprise' => $inventory->getEnterprise(), 'hasStock' => 1]);
            $available = 0;
            foreach ($products as $product) {
                $inventoryAvailability = new InventoryAvailability();
                $inventoryAvailability->setAvailable($available)
                    ->setInventory($inventory)
                    ->setProduct($product);

                $inventory->addInventoryAvailability($inventoryAvailability);
                $product->addInventoryAvailability($inventoryAvailability);

                $manager->persist($inventoryAvailability);
                $manager->persist($product);
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
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     * @return Response
     */
    public function edit(Inventory $inventory, Request $request, InventoryRepository $inventoryRepo, EntityManagerInterface $manager)
    { //@Security("is_granted('ROLE_STOCK_MANAGER')", message = "Vous n'avez pas le droit d'accéder à cette ressource")

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
     * @Route("/inventory/{id<\d+>}/delete", name="inventory_delete")
     *
     * @IsGranted("ROLE_STOCK_MANAGER")
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

    /**
     * @Route("/inventory/{id<\d+>}/dashboard", name="inventory_dashboard")
     * 
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     */
    public function inventoryDash(Inventory $inventory, EntityManagerInterface $manager, InventoryRepository $inventoryRepo)
    {
        $productStats          = [];
        $inventoryAvailability = [];
        $products = $manager->createQuery("SELECT p
                                           FROM App\Entity\Product p, App\Entity\Lot l
                                           JOIN l.inventory inv
                                           WHERE inv.id = :invId
                                           AND p.hasStock = 1
                                           AND l.product = p.id
        ")
            ->setParameters(array(
                'invId'   => $inventory->getId(),
            ))
            ->getResult();
        //dd($products);

        $to_ = new DateTime('now');
        //dump($to_->format('Y-m-d H:i:s'));
        $from_ = new DateTime('now');
        $from_->sub(new DateInterval('P30D'));
        //dump($from_->format('Y-m-d H:i:s'));
        $from_ = new DateTime($from_->format('Y-m-d') . ' ' . '00:00:00');
        //dump($from_->format('Y-m-d H:i:s'));
        /*
         
         
                                                                        AND st.createdAt >= :from_*/
        foreach ($products as $product) {
            //dd($product->getId());
            $productStats['' . $product->getId()] = $manager->createQuery("SELECT AVG(st.quantity) AS qtyAVG, 
                                                                        SUM(st.quantity) AS qtyTotal, MAX(st.quantity) AS qtyMax, 
                                                                        MIN(st.quantity) AS qtyMin, STD(st.quantity) AS ET
                                                                        FROM App\Entity\StockMovement st
                                                                        JOIN st.commercialSheet cms
                                                                        JOIN st.lot l
                                                                        JOIN l.product p
                                                                        WHERE st.type = 'Sale Exit'
                                                                        AND (st.createdAt >= :from_ AND st.createdAt <= :to_)                                                                                   
                                                                        AND p.id = :prodId
                                                                        AND (cms.deliveryStatus = 1 OR cms.completedStatus = 1)
                                                                    ")
                ->setParameters(array(
                    'from_'    => $from_->format('Y-m-d H:i:s'),
                    'to_'      => $to_->format('Y-m-d H:i:s'),
                    'prodId'   => $product->getId()
                ))
                ->getResult();
            $inventoryAvailabilityRepo = $manager->getRepository("App:InventoryAvailability");
            $inventoryAvailability['' . $product->getId()] = $inventoryAvailabilityRepo->findOneBy(['inventory' => $inventory, 'product' => $product]);
        }
        //dump($inventoryAvailability);
        //dd($productStats);

        $inventories = $inventoryRepo->findAll();

        return $this->render('inventory/inventory_dashboard.html.twig', [
            'inventories'  => $inventories,
            'inventory'    => $inventory,
            'available'    => $inventoryAvailability,
            'productStats' => $productStats,
            'products'     => $products,

        ]);
    }

    /**
     * Permet la MAJ du tableau des mouvements de stock
     * 
     * @Route("/stock/movement/table/update/", name="stock_movement_update") 
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function updateStockMovementTable(Request $request, EntityManagerInterface $manager)
    {
        $paramJSON = $this->getJSONRequest($request->getContent());
        if ((array_key_exists("startDate", $paramJSON) && !empty($paramJSON['startDate'])) && (array_key_exists("endDate", $paramJSON) && !empty($paramJSON['endDate'])) && (array_key_exists("inv", $paramJSON) && !empty($paramJSON['inv']))) {
            $startDate = new DateTime($paramJSON['startDate']);
            $endDate = new DateTime($paramJSON['endDate']);

            $nowTime = new DateTime("now");
            $nowTime = $nowTime->format('d/m/Y');
            //$nowTime = $nowTime->format('H:m:i');
            //dump($endDate);
            //dump($endDate->format('d-m-Y'));
            //dump($nowTime);

            if ($endDate->format('d/m/Y') == $nowTime) {
                $nowTime = new DateTime("now");
                $nowTime = $nowTime->format('H:m:i');
                $endDate = new DateTime($paramJSON['endDate'] . ' ' . $nowTime);
                //dump($nowTime);
            } else {
                $endDate = new DateTime($paramJSON['endDate'] . ' 23:59:59');
            }
            // $startDate = new DateTime("yesterday");
            // $endDate = new DateTime("now");
            $inv = $paramJSON['inv'];
            //dump($endDate);
            $stockMovements = $manager->createQuery("SELECT st.createdAt AS dat, p.sku AS sku, p.name AS nam, 
                                                    l.number AS numLot, l.dlc AS dlc, st.type AS typ, st.quantity AS qty
                                                    FROM App\Entity\StockMovement st
                                                    JOIN st.lot l 
                                                    JOIN l.inventory inv
                                                    JOIN l.product p
                                                    WHERE st.createdAt >= :startDate
                                                    AND st.createdAt <= :endDate
                                                    AND inv.id = :invId
                                                                                                                                        
                                                ")
                ->setParameters(array(
                    'startDate'    => $startDate->format('Y-m-d H:i:s'),
                    'endDate'      => $endDate->format('Y-m-d H:i:s'),
                    'invId'        => $inv
                ))
                ->getResult();
            //dump($stockMovements);
            return $this->json([
                'code'           => 200,
                'stockMovements' => $stockMovements,
            ], 200);
        }
        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 200);
    }
}
