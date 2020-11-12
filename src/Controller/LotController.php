<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Lot;
use App\Form\LotType;
//use App\Entity\Product;
//use App\Entity\Inventory;
use App\Entity\StockMovement;
//use App\Repository\LotRepository;
//use App\Repository\InventoryRepository;
use App\Entity\InventoryAvailability;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LotController extends ApplicationController
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
            if ($inventory) $lots = $manager->getRepository('App:Lot')->findBy(['product' => $product, 'inventory' => $inventory]);
            else $lots = $manager->getRepository('App:Lot')->findBy(['product' => $product]);
            $isProd = $product->getId();
        } else if ($inventory) {
            $lots = $manager->getRepository('App:Lot')->findBy(['inventory' => $inventory]);
            $inv_  = $inventory->getId();
            //dump($inventory);
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
            //dump($product);
            $include_product = false;
            if ($inventory) {
                $lot->setInventory($inventory);
                $include_inventory = false;
                //dump($inventory);
            }
        } else if ($inventory) {
            $lot->setInventory($inventory);
            $include_inventory = false;
            //dump($inventory);
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
            if ($inventoryAvailability) {
                $add = $inventoryAvailability->getAvailable() + $lot->getQuantity();
                $inventoryAvailability->setAvailable($add);
            } else {
                $inventoryAvailability = new InventoryAvailability();
                $inventoryAvailability->setInventory($lot->getInventory())
                    ->setProduct($lot->getProduct())
                    ->setAvailable($lot->getQuantity());

                $lot->getInventory()->addInventoryAvailability($inventoryAvailability);
                $lot->getProduct()->addInventoryAvailability($inventoryAvailability);

                $manager->persist($inventory);
                $manager->persist($product);
                $manager->persist($inventoryAvailability);
            }

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

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($lot);
            $manager->flush();

            $this->addFlash(
                'success',
                "The Lot N° <strong> {$lot->getNumber()} </strong> has been registered successfully !"
            );
            return $this->redirectToRoute('lots_index', [
                'prod' => $include_product   == true ? 0 : $lot->getProduct()->getId(),
                'inv'  => $include_inventory == true ? 0 : $lot->getInventory()->getId(),
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
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_STOCK_MANAGER') and lot.getInventory().getEnterprise() === user.getEnterprise() )" )
     * 
     */
    public function edit(Lot $lot, Request $request, EntityManagerInterface $manager)
    {
        $include_product = false;
        $include_inventory = false;

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
                    $add = $add >= 0 ? $add : 0;
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

            if ($oldInv != $lot->getInventory()) { //Si l'inventaire a été modifié
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
                'prod' => 0, //$include_product   == true ? $lot->getProduct()->getId() : 0,
                'inv'  => $lot->getInventory()->getId(), //$include_inventory == true ? $lot->getInventory()->getId() : 0,
            ]);
        }

        return $this->render('lot/edit.html.twig', [
            'form'              => $form->createView(),
            'inventories'       => $inventories,
            'include_product'   => $include_product,
            'include_inventory' => $include_inventory
        ]);
    }

    /**
     * Permet le retrait total de la quantité restante en stock des lots périmés
     * 
     * @Route("/remove/expired/lots", name="lot_expired")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function removeExpiredLot(Request $request, EntityManagerInterface $manager)
    {
        //dump($request->request->get("lotIds"));

        //$paramJSON = $this->getJSONRequest($request->getContent());
        //$paramJSON = $request->request->get("lotIds");
        $paramJSON = $this->getJSONRequest($request->getContent());
        if (array_key_exists("lotIds", $paramJSON)) {
            //dump($paramJSON);

            if (!empty($paramJSON['lotIds'])) {
                $lotRepo   = $manager->getRepository('App:Lot');

                foreach ($paramJSON['lotIds'] as $id) {
                    $lot = $lotRepo->findOneBy(['id' => intval($id)]);
                    $inventoryAvailability = $manager->getRepository('App:InventoryAvailability')->findOneBy(['inventory' => $lot->getInventory(), 'product' => $lot->getProduct()]);
                    //Gestion du mouvement de stock : Manual Exit
                    $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
                    $stockMovement = new StockMovement();
                    $stockMovement->setCreatedAt($date)
                        ->setLot($lot)
                        ->setQuantity($lot->getQuantity())
                        ->setType('Expiration Exit');

                    //MAJ de la disponibilité
                    $diff = $inventoryAvailability->getAvailable() - $lot->getQuantity();
                    if ($diff > 0) $inventoryAvailability->setAvailable($diff);
                    else $inventoryAvailability->setAvailable(0);

                    $lot->setQuantity(0);

                    // dump($stockMovement);
                    // dd($inventoryAvailability);

                    $manager->persist($stockMovement);
                    $manager->persist($inventoryAvailability);
                    $manager->persist($lot);
                }
                $manager->flush();
                return $this->json([
                    'code' => 200,
                    'message' => 'Expiration exit successfull !',
                ], 200);
            }
        }
        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not exists !',
        ], 403);
    }
}
