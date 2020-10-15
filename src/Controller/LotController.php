<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Lot;
use App\Form\LotType;
use App\Entity\Product;
use App\Entity\Inventory;
use App\Entity\StockMovement;
use App\Repository\LotRepository;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LotController extends AbstractController
{
    /**
     * @Route("/lots/dashboard/{prod<\d+>?0}/{inv<\d+>?0}", name="lots_index")
     * 
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     */
    public function index($prod, $inv, EntityManagerInterface $manager)
    {
        $lots = [];
        $isProd = false;
        $inv_    = 0;
        $product = $manager->getRepository('App:Product')->findOneBy(['id' => $prod]);
        $inventory = $manager->getRepository('App:Inventory')->findOneBy(['id' => $inv]);
        if ($product) {
            $lots = $manager->getRepository('App:Lot')->findBy(['product' => $product]);
            $isProd = $product->getId();
        } else if ($inventory) {
            $lots = $manager->getRepository('App:Lot')->findBy(['inventory' => $inventory]);
            $inv_  = $inventory->getId();
            dump($inventory);
        }
        //dd($lots);
        $inventories = $manager->getRepository('App:Inventory')->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        return $this->render('lot/index_lots.html.twig', [
            'inventories' => $inventories,
            'lots'        => $lots,
            'isProd'      => $isProd,
            'inv'         => $inv_,

        ]);
    }

    /**
     * @Route("/lot/{prod<\d+>?0}/{inv<\d+>?0}/create", name="lot_create")
     * 
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     */
    public function create($prod, $inv, Request $request, EntityManagerInterface $manager)
    {
        $lot = new Lot();
        $include_product = true;
        $include_inventory = true;
        $product = $manager->getRepository('App:Product')->findOneBy(['id' => $prod]);
        $inventory = $manager->getRepository('App:Inventory')->findOneBy(['id' => $inv]);
        if ($product) {
            $lot->setProduct($product);
            dump($product);
            $include_product = false;
            if ($inventory) {
                $lot->setInventory($inventory);
                $include_inventory = false;
                dump($inventory);
            }
        } else if ($inventory) {
            $lot->setInventory($inventory);
            $include_inventory = false;
            dump($inventory);
        }

        $inventories = $manager->getRepository('App:Inventory')->findBy(['enterprise' => $this->getUser()->getEnterprise()]);

        //  instancier un form externe
        $form = $this->createForm(LotType::class, $lot, [
            'entId'             => $this->getUser()->getEnterprise()->getId(),
            'include_product'   => $include_product,
            'include_inventory' => $include_inventory
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //MAJ de la disponibilité
            $inventoryAvailability = $manager->getRepository('App:InventoryAvailability')->findOneBy(['inventory' => $lot->getInventory(), 'product' => $lot->getProduct()]);
            $add = $inventoryAvailability->getAvailable() + $lot->getQuantity();
            $inventoryAvailability->setAvailable($add);

            //Gestion du mouvement de stock : input
            $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
            $stockMovement = new StockMovement();
            $stockMovement->setCreatedAt($date)
                ->setLot($lot)
                ->setQuantity($lot->getQuantity())
                ->setType('In');
            // dump($stockMovement);
            // dd($inventoryAvailability);
            $manager->persist($stockMovement);
            $manager->persist($inventoryAvailability);

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($lot);
            $manager->flush();

            $this->addFlash(
                'success',
                "The Lot N° <strong> {$lot->getNumber()} </strong> has been registered successfully !"
            );
            return $this->redirectToRoute('lots_index', [
                'prod' => $include_product   == true ? $lot->getProduct()->getId() : 0,
                'inv'  => $include_inventory == true ? $lot->getInventory()->getId() : 0,
            ]);
        }

        return $this->render('lot/new.html.twig', [
            'form'              => $form->createView(),
            'inventories'       => $inventories,
            'include_product'   => $include_product,
            'include_inventory' => $include_inventory
        ]);
    }

    /**
     * @Route("/lot/{id<\d+>}/edit", name="lot_edit")
     * 
     * @IsGranted("ROLE_STOCK_MANAGER")
     * 
     */
    public function edit(Lot $lot, Request $request, EntityManagerInterface $manager)
    {
        $include_product = true;
        $include_inventory = true;

        $inventories = $manager->getRepository('App:Inventory')->findBy(['enterprise' => $this->getUser()->getEnterprise()]);

        $oldQty = $lot->getQuantity();
        $oldInv = $lot->getInventory();
        //  instancier un form externe
        $form = $this->createForm(LotType::class, $lot, [
            'entId' => $this->getUser()->getEnterprise()->getId(),
            'include_product'   => $include_product,
            'include_inventory' => $include_inventory
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inventoryAvailability = $manager->getRepository('App:InventoryAvailability')->findOneBy(['inventory' => $lot->getInventory(), 'product' => $lot->getProduct()]);

            $diff = $oldQty - $lot->getQuantity();
            if ($diff != 0) {
                //Gestion du mouvement de stock : Manual Exit
                $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
                $stockMovement = new StockMovement();
                $stockMovement->setCreatedAt($date)
                    ->setLot($lot)
                    ->setQuantity(abs($diff));

                //MAJ de la disponibilité
                if ($diff > 0) {
                    $add = $inventoryAvailability->getAvailable() - $diff;
                    $inventoryAvailability->setAvailable($add);
                    $stockMovement->setType('Manual Exit');
                } else {
                    $add = $inventoryAvailability->getAvailable() + abs($diff);
                    $inventoryAvailability->setAvailable($add);
                    $stockMovement->setType('In');
                }
                // dump($stockMovement);
                // dd($inventoryAvailability);
                $manager->persist($stockMovement);
                $manager->persist($inventoryAvailability);
            }

            if ($oldInv != $lot->getInventory()) {
                $inventoryAvailability->setInventory($lot->getInventory());
                // dd($inventoryAvailability);
                $manager->persist($inventoryAvailability);
            }
            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($lot);
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of the Lot N° <strong> {$lot->getNumber()} </strong> have been saved !"
            );
            return $this->redirectToRoute('lots_index', [
                'prod' => $include_product   == true ? $lot->getProduct()->getId() : 0,
                'inv'  => $include_inventory == true ? $lot->getInventory()->getId() : 0,
            ]);
        }

        return $this->render('lot/edit.html.twig', [
            'form'              => $form->createView(),
            'inventories'       => $inventories,
            'include_product'   => $include_product,
            'include_inventory' => $include_inventory
        ]);
    }
}
