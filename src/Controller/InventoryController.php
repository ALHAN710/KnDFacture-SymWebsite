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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security( "( is_granted('ROLE_STOCK_MANAGER') and user.getEnterprise().getIsActivated() == true ) " )
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
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_STOCK_MANAGER') and inventory.getEnterprise() === user.getEnterprise() )" )
     * 
     */
    public function inventoryDash(Inventory $inventory, EntityManagerInterface $manager, InventoryRepository $inventoryRepo)
    {
        $productStats          = [];
        //$Stats          = [];
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

        foreach ($products as $product) {
            //dd($product->getId());
            /*$productStats['' . $product->getId()] = $manager->createQuery("SELECT AVG(st.quantity) AS qtyAVG, 
                                                                        SUM(st.quantity) AS qtyTotal, MAX(st.quantity) AS qtyMax, 
                                                                        MIN(st.quantity) AS qtyMin, STD(st.quantity) AS ET
                                                                        FROM App\Entity\StockMovement st
                                                                        JOIN st.commercialSheet cms
                                                                        JOIN cms.inventory inv
                                                                        JOIN st.lot l
                                                                        JOIN l.product p
                                                                        WHERE st.type = 'Sale Exit'
                                                                        AND (st.createdAt >= :from_ AND st.createdAt <= :to_)                                                                                   
                                                                        AND p.id = :prodId
                                                                        AND (cms.deliveryStatus = 1 OR cms.completedStatus = 1)
                                                                        AND inv.id = :invId
                                                                        
                                                                    ")
                ->setParameters(array(
                    'from_'    => $from_->format('Y-m-d H:i:s'),
                    'to_'      => $to_->format('Y-m-d H:i:s'),
                    'prodId'   => $product->getId(),
                    'invId'    => $inventory->getId(),
                ))
                ->getResult();*/
            /*$Stats['' . $product->getId()] = $manager->createQuery("SELECT SUBSTRING(cms.createdAt, 1, 10) AS jour, SUM(st.quantity) AS qtyTotal
                                                                        FROM App\Entity\StockMovement st
                                                                        JOIN st.commercialSheet cms
                                                                        JOIN cms.inventory inv
                                                                        JOIN st.lot l
                                                                        JOIN l.product p
                                                                        WHERE st.type = 'Sale Exit'
                                                                        AND (st.createdAt >= :from_ AND st.createdAt <= :to_)                                                                                   
                                                                        AND p.id = :prodId
                                                                        AND (cms.deliveryStatus = 1 OR cms.completedStatus = 1)
                                                                        AND inv.id = :invId
                                                                        GROUP BY jour
                                                                        ORDER BY qtyTotal ASC
                                                                    ")
                ->setParameters(array(
                    'from_'    => $from_->format('Y-m-d H:i:s'),
                    'to_'      => $to_->format('Y-m-d H:i:s'),
                    'prodId'   => $product->getId(),
                    'invId'    => $inventory->getId(),
                ))
                ->getResult();

            $sum = 0.0;
            $AVG = 0.0;
            $nb = count($Stats['' . $product->getId()]);
            $min = $Stats['' . $product->getId()][0]['qtyTotal'];
            $max = $Stats['' . $product->getId()][$nb - 1]['qtyTotal'];
            foreach ($Stats['' . $product->getId()] as $key => $value) {
                $sum += $value['qtyTotal'];
            }
            $AVG = $sum / ($nb * 1.0);

            $ET = 0;
            foreach ($Stats['' . $product->getId()] as $key => $value) {
                $ET += pow(($value['qtyTotal'] - $AVG), 2);
            }

            $ET = $ET / ($nb - 1);
            $ET = pow($ET, 1 / 2);
            $ET = number_format((float) $ET, 2, '.', '');
            $AVG = number_format((float) $AVG, 2, '.', '');
            $productStats[$product->getId()] = [
                'AVG' => $AVG,
                'ET'  => $ET,
                'MAX' => $max,
                'MIN' => $min,
            ];*/
            $inventoryAvailabilityRepo = $manager->getRepository("App:InventoryAvailability");
            $inventoryAvailability['' . $product->getId()] = $inventoryAvailabilityRepo->findOneBy(['inventory' => $inventory, 'product' => $product]);
        }
        //dump($Stats);

        //dd($productStats);
        //dump($inventoryAvailability);
        //dd($productStats);

        $inventories = $inventoryRepo->findAll();

        return $this->render('inventory/inventory_dashboard.html.twig', [
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
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function updateStockMovementTable(Request $request, EntityManagerInterface $manager)
    {
        $paramJSON = $this->getJSONRequest($request->getContent());
        //dump($paramJSON);
        if ((array_key_exists("startDate", $paramJSON) && !empty($paramJSON['startDate'])) && (array_key_exists("endDate", $paramJSON) && !empty($paramJSON['endDate'])) && (array_key_exists("inv", $paramJSON) && !empty($paramJSON['inv']))) {
            $startDate = new DateTime($paramJSON['startDate']);
            $endDate = new DateTime($paramJSON['endDate']);
            $nb = 0;
            $nowTime = new DateTime("now");
            //$nowTime = $nowTime->format('d/m/Y');
            //$nowTime = $nowTime->format('H:m:i');
            //dump($endDate);
            //dump($endDate->format('d-m-Y'));
            //dump($nowTime);
            //dump($nowTime->format('Y-m-d H:i:s'));
            if ($endDate == $startDate) {
                //$nowTime = new DateTime("now");
                //dump($nowTime);
                $endDate = new DateTime($paramJSON['endDate'] . ' 23:59:59');
                $nb = 1;
                //dump($endDate->format('Y-m-d H:i:s'));
            } else {
                if ($endDate->format('Y-m-d') == $nowTime->format('Y-m-d')) {
                    $nowTime = $nowTime->format('H:i:s');
                    //dump($nowTime);
                    $endDate = new DateTime($paramJSON['endDate'] . ' ' . $nowTime);
                } else $endDate = new DateTime($paramJSON['endDate'] . ' 23:59:59');
                $tmp = $startDate;
                $interval = $tmp->diff($endDate);

                $nb = $interval->days; //Nombre de jour total de différence entre les dates 
            }
            // $startDate = new DateTime("yesterday");
            // $endDate = new DateTime("now");
            $inv = $paramJSON['inv'];
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
            foreach ($stockMovements as $key => $value) {
                $stockMovements[$key]['dat'] = $value['dat']->format('d M Y H:i:s');
                $stockMovements[$key]['dlc'] = $value['dlc']->format('d M Y');
            }
            //dump($stockMovements);

            $productStats  = null;
            $Stats         = [];
            $inventoryAvailability = [];

            $products = $manager->createQuery("SELECT p
                                           FROM App\Entity\Product p, App\Entity\Lot l
                                           JOIN l.inventory inv
                                           WHERE inv.id = :invId
                                           AND p.hasStock = 1
                                           AND l.product = p.id
                                        ")
                ->setParameters(array(
                    'invId'   => $inv,
                ))
                ->getResult();
            //dump($products);

            $inventory = $manager->getRepository("App:Inventory")->findOneBy(['id' => $inv]);
            $inventoryAvailabilityRepo = $manager->getRepository("App:InventoryAvailability");

            //dump($inventory);

            foreach ($products as $product) {

                $Stats['' . $product->getId()] = $manager->createQuery("SELECT SUBSTRING(cms.createdAt, 1, 10) AS jour, SUM(st.quantity) AS qtyTotal
                                                                        FROM App\Entity\StockMovement st
                                                                        JOIN st.commercialSheet cms
                                                                        JOIN cms.inventory inv
                                                                        JOIN st.lot l
                                                                        JOIN l.product p
                                                                        WHERE st.type = 'Sale Exit'
                                                                        AND (st.createdAt >= :from_ AND st.createdAt <= :to_)                                                                                   
                                                                        AND p.id = :prodId
                                                                        AND (cms.deliveryStatus = 1 OR cms.completedStatus = 1)
                                                                        AND inv.id = :invId
                                                                        GROUP BY jour
                                                                        ORDER BY qtyTotal ASC
                                                                    ")
                    ->setParameters(array(
                        'from_'    => $startDate->format('Y-m-d H:i:s'),
                        'to_'      => $endDate->format('Y-m-d H:i:s'),
                        'prodId'   => $product->getId(),
                        'invId'    => $inv,
                    ))
                    ->getResult();

                //dump($Stats);
                $sum = 0.0;
                $AVG = 0.0;
                // $min = 0;
                // $max = 0;
                $ET  = 0;

                if ($nb > 0) {
                    // $min =  array_key_exists(0, $Stats) ? $Stats['' . $product->getId()][0]['qtyTotal'] : 0;
                    // $max = array_key_exists($nb - 1, $Stats) ? $Stats['' . $product->getId()][$nb - 1]['qtyTotal'] : 0;
                    foreach ($Stats['' . $product->getId()] as $key => $value) {
                        $sum += $value['qtyTotal'];
                    }
                    $AVG = $sum / ($nb * 1.0);

                    $ET = 0;
                    foreach ($Stats['' . $product->getId()] as $key => $value) {
                        $ET += pow(($value['qtyTotal'] - $AVG), 2);
                    }

                    $ET = $ET / ($nb);
                    $ET = pow($ET, 1 / 2);
                }

                //dump($nb);
                $ET = number_format((float) $ET, 2, '.', '');
                $AVG = number_format((float) $AVG, 2, '.', '');

                $productStats[] = [
                    'id'   => $product->getId(),
                    'Sku'  => $product->getSku(),
                    'Name' => $product->getName(),
                    'AVG'  => $AVG,
                    'ET'   => $ET,
                    // 'MAX'  => $max,
                    // 'MIN'  => $min,
                ];

                $inventoryAvailability[] = [
                    'av' => $inventoryAvailabilityRepo->findOneBy(['inventory' => $inventory->getId(), 'product' => $product->getId()])->getAvailable(),
                    'id' => $product->getId(),
                ];
            }
            //dump($Stats);
            //dump($inventoryAvailability);
            //dd($productStats);
            return $this->json([
                'code'           => 200,
                'available'      => $inventoryAvailability,
                'productStats'   => $productStats,
                'stockMovements' => $stockMovements,
            ], 200);
        }
        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 200);
    }
}
