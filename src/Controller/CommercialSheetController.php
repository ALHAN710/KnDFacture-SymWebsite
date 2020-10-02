<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Product;
use App\Entity\Inventory;
use App\Entity\OrderItem;
use App\Entity\StockMovement;
use App\Entity\BusinessContact;
use App\Entity\CommercialSheet;
use App\Form\CommercialSheetType;
use App\Repository\LotRepository;
use App\Repository\ProductRepository;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use App\Repository\CommercialSheetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\InventoryAvailabilityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Contrôleur des commandes
 * 
 */
class CommercialSheetController extends ApplicationController
{
    /**
     * @Route("/commercial/sheet/{id<\d+>}/{type<[a-z]+>}/dashboard", name="commercial_sheet_index")
     * @IsGranted("ROLE_MANAGER")
     */
    public function index(Inventory $inventory, $type, CommercialSheetRepository $commercialSheetRepo, InventoryRepository $inventoryRepo)
    {
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        $commercialSheets_     = $commercialSheetRepo->findBy(['inventory' => $inventory]);
        $commercialSheets      = [];
        foreach ($commercialSheets_ as $commercialSheet) {
            if ($commercialSheet->getType() == $type) {
                $commercialSheets[] = $commercialSheet;
            }
        }
        return $this->render('commercial_sheet/index_commercial_sheet.html.twig', [
            'commercialSheets'      => $commercialSheets,
            'inventory'             => $inventory,
            'inventories'           => $inventories,
            'type'                  => $type,
        ]);
    }


    /**
     * Permet de créer une commande (order)
     *
     * @Route("/commercial/sheet/new/{id<\d+>}/{type<[a-z]+>}/{stock<\d+>?0}", name = "commercial_sheet_create")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     * @return Response
     */
    public function create(BusinessContact $businessContact, $type, $stock, Request $request, InventoryRepository $inventoryRepo, LotRepository $lotRepo, EntityManagerInterface $manager, InventoryAvailabilityRepository $inventoryAvailabilityRepo)
    { //
        $commercialSheet = new CommercialSheet();
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);

        $availabilities = [];
        $inventoryAvailabilities = [];

        if ($businessContact) {
            $commercialSheet->setBusinessContact($businessContact)
                ->setUser($this->getUser())
                ->setType($type);
            if ($type == 'bill') {
                $inventory_ = $inventoryRepo->findOneBy(['id' => $stock, 'type' => 'PF']);
            } else if ($type == 'quote') {
                $inventory_ = $inventoryRepo->findOneBy(['id' => $stock]);
            }
            if ($inventory_) { //Si l'inventaire existe
                $commercialSheet->setInventory($inventory_);
                //$lots = $inventory_->getLots();
                //Recherche des disponibilités relatif à l'inventaire passé en paramètre à la route
                foreach ($inventories as $inventory) {
                    if ($inventory == $inventory_) {
                        $inventoryAvailabilities = $inventoryAvailabilityRepo->findBy(['inventory' => $inventory]);
                        break;
                    }
                }

                foreach ($inventoryAvailabilities as $inventoryAvailability) {
                    $productId = $inventoryAvailability->getProduct()->getId();
                    $availabilities['' . $productId] = $inventoryAvailability->getAvailable();
                }
            } else {
                //Exception de l'erreur Inventory non défini pour ce document
            }
        } else {
            //Exception de l'erreur Customer/Supplier non défini pour ce document
        }

        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        //dump($inventory_);
        /*$choices = [];
        foreach ($inventories as $inventory) {
            $choices['' . strtoupper($inventory)] = ucfirst($inventory);
        }*/

        //  instancier un form externe
        $form = $this->createForm(CommercialSheetType::class, $commercialSheet);
        $form->handleRequest($request);

        //dump($availabilities);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
            //dump($commercialSheet->getOrderItems());
            foreach ($commercialSheet->getOrderItems() as $orderItem) {
                if ($orderItem->getQuantity() >= 1) { //On considère uniquement les items de Qty >= 1
                    if ($orderItem->getOfferType() == 'Product') { //Gestion des items de type Product
                        //Recherche de la disponibilité du produit contenu dans l'item dans l'inventaire passé en paramètre
                        $inventoryAvailability = $inventoryAvailabilityRepo->findOneBy(['inventory' => $inventory_, 'product' => $orderItem->getProduct()]);
                        //dump($inventoryAvailability);
                        if ($inventoryAvailability) { //Si cette disponibilité existe la mettre à jour ainsi que la quantité des lots relatifs à ce produit
                            if ($orderItem->getQuantity() <= $inventoryAvailability->getAvailable()) {
                                $inventoryAvailability->setAvailable($orderItem->getAvailable());
                                $manager->persist($inventoryAvailability);

                                //Recherche des lots relatifs à ce produit dans l'inventaire reçu ordonné suivant le mode de management 
                                //de ce dernier
                                $order_ = $inventory_->getManagementMode() == 'FIFO' ? 'asc' : 'desc';
                                //dump($orderItem->getProduct());
                                $lots = $lotRepo->findBy(['inventory' => $inventory_, 'product' => $orderItem->getProduct()], ['dlc' => $order_]);
                                if (!empty($lots)) {
                                    $qtyToRemove = $orderItem->getQuantity();
                                    foreach ($lots as $lot) {
                                        if ($lot->getQuantity() > 0) {
                                            dump($lot);
                                            $diff = $lot->getQuantity() - $qtyToRemove;
                                            if ($diff >= 0) {
                                                $qty = $diff == 0 ? $qtyToRemove : $diff;
                                                $lot->setQuantity($qty);
                                                $manager->persist($lot); //Sauvegarde du lot en BDD

                                                //Gestion du mouvement de stock
                                                $stockMovement = new StockMovement();
                                                $stockMovement->setCreatedAt($date)
                                                    ->setLot($lot)
                                                    ->setQuantity($qty)
                                                    ->setType('Sale Exit');
                                                dump($stockMovement);
                                                $manager->persist($stockMovement);
                                                break;
                                            } else {
                                                $qtyToRemove = $qtyToRemove - $lot->getQuantity();

                                                //Gestion du mouvement de stock
                                                $stockMovement = new StockMovement();
                                                $stockMovement->setCreatedAt($date)
                                                    ->setLot($lot)
                                                    ->setQuantity($lot->getQuantity())
                                                    ->setType('Sale Exit');
                                                dump($stockMovement);
                                                $manager->persist($stockMovement);

                                                $lot->setQuantity(0);
                                                $manager->persist($lot); //Sauvegarde du lot en BDD
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        /*foreach ($inventoryAvailabilities as $inventoryAvailability) {
                            if ($orderItem->getProduct()->getId() == $inventoryAvailability->getProduct()->getId()) {
                                $inventoryAvailability->setAvailable($orderItem->getAvailable());
                                $manager->persist($inventoryAvailability);
                            }
                            $availabilities['' . $productId] = $inventoryAvailability->getAvailable();
                            break;
                        }*/
                        $manager->persist($orderItem);
                    } else if ($orderItem->getOfferType() == 'Service') { //Gestion des items de type Service pour création
                        $service = new Product();
                        $service->setEnterprise($this->getUser()->getEnterprise())
                            ->setName($orderItem->getOfferIn())
                            ->setSku('S1001')
                            ->setPrice($orderItem->getPriceIn())
                            ->setType('Service');
                        $service->addOrderItem($orderItem);
                        $manager->persist($service);
                        $manager->persist($orderItem);
                        //$orderItem->setProduct($service);
                        dump($orderItem);
                    }
                }
                $orderItem->addCommercialSheet($commercialSheet);
            }

            if ($commercialSheet->getDeliveryStatus() == true) {
                $commercialSheet->setDeliverAt($date);
            }
            if ($commercialSheet->getPaymentStatus() == true) {
                $commercialSheet->setPayAt($date);
            }
            if ($commercialSheet->getCompletedStatus() == true) {
                $commercialSheet->setDeliveryStatus(true)
                    ->setPaymentStatus(true)
                    ->setDeliverAt($date)
                    ->setPayAt($date)
                    ->setCompletedAt($date);
            }

            /*foreach ($order->getReductions() as $reduction) {
                dump($reduction);
                $reduction->addOrder($order);
                $manager->persist($reduction);
            }*/

            //die();
            $manager->persist($commercialSheet);
            $manager->flush();

            $this->addFlash(
                'success',
                "The {$commercialSheet->getType()} ID #<strong>{$commercialSheet->getNumber()} </strong> has been registered successfully !"
            );

            return $this->redirectToRoute('commercial_sheet_print', [
                'inventories' => $inventories,
                'id' => $commercialSheet->getId(),
            ]);
        }

        return $this->render(
            'commercial_sheet/new.html.twig',
            [
                'form'            => $form->createView(),
                'businessContact' => $businessContact,
                'commercialSheet' => $commercialSheet,
                'availabilities'  => $availabilities,
                'inventories'     => $inventories,
            ]
        );
    }

    /**
     * Permet d'afficher le formulaire d'édition d'une commande (order)
     *
     * @Route("/commercial/sheet/{id<\d+>}/edit", name="commercial_sheet_edit")
     * 
     * @Security("is_granted('ROLE_MANAGER')", message = "Vous n'avez pas le droit d'accéder à cette ressource")
     * 
     * @return Response
     */
    public function edit(CommercialSheet $commercialSheet, Request $request, EntityManagerInterface $manager, InventoryRepository $inventoryRepo, InventoryAvailabilityRepository $inventoryAvailabilityRepo)
    { //

        dump($commercialSheet);
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //  instancier un form externe
        $form = $this->createForm(CommercialSheetType::class, $commercialSheet, array(
            //'orderItem' => $order->getOrderItems(),
        ));
        $form->handleRequest($request);
        $availabilities = [];
        $inventoryAvailabilities = [];
        //$inventoryAvailabilities = $inventoryAvailabilityRepo->findBy(['inventory' => $commercialSheet->getCustomer()->getDeliveryAddress()->getInventory()]);
        foreach ($inventoryAvailabilities as $inventoryAvailability) {
            $productId = $inventoryAvailability->getProduct()->getId();
            $availabilities['' . $productId] = $inventoryAvailability->getAvailable();
        }
        //dump($availabilities);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($commercialSheet->getOrderItems());
            foreach ($commercialSheet->getOrderItems() as $orderItem) {
                if ($orderItem->getQuantity() >= 1) {
                    foreach ($inventoryAvailabilities as $inventoryAvailability) {
                        if ($orderItem->getProduct()->getId() == $inventoryAvailability->getProduct()->getId()) {
                            $inventoryAvailability->setAvailable($orderItem->getAvailable());
                            $manager->persist($inventoryAvailability);
                        }
                        $availabilities['' . $productId] = $inventoryAvailability->getAvailable();
                    }
                    dump($orderItem);
                    $manager->persist($orderItem);
                }
                $orderItem->addOrder($commercialSheet);
            }
            /*foreach ($order->getReductions() as $reduction) {
                dump($reduction);
                $reduction->addOrder($order);
                $manager->persist($reduction);
            }*/

            $manager->persist($commercialSheet);
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of the Order ID #<strong>{$commercialSheet->getNumber()} </strong> have been saved !"
            );

            return $this->redirectToRoute('commercial_sheet_bill', [
                'inventories' => $inventories,
            ]);
        }

        return $this->render('commercial_sheet/edit.html.twig', [
            'form'            => $form->createView(),
            'commercialSheet' => $commercialSheet,
            'availabilities'  => $availabilities,
            'inventories'     => $inventories,
        ]);
    }

    /**
     * Permet de supprimer une commande (order)
     * 
     * @Route("/commercial/sheet/{id}/delete", name="commercial_sheet_delete")
     * 
     * @IsGranted("ROLE_MANAGER")
     *
     * @param CommercialSheet $commercialSheet
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(CommercialSheet $commercialSheet, InventoryRepository $inventoryRepo, EntityManagerInterface $manager)
    {
        $manager->remove($commercialSheet);
        $manager->flush();

        $this->addFlash(
            'success',
            "The removal of {$commercialSheet->getType()} ID #<strong>{$commercialSheet->getNumber()} </strong> has been completed successfully !"
        );
        $inventories = $inventoryRepo->findAll();
        $id = null;
        foreach ($inventories as $inventory) {
            if (!empty($inventory)) {
                $id = $inventory->getId();
                break;
            }
        }
        return $this->redirectToRoute("order_index", ['id' => $id]);
    }

    /**
     * Permet de convertir un devis(quote) en facture(bill)
     * 
     * @Route("/commercial/sheet/{id<\d+>}/convert", name="commercial_sheet_convert")
     *
     * @param CommercialSheet $commercialSheet
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function convert(CommercialSheet $commercialSheet, EntityManagerInterface $manager)
    {
        $commercialSheet->setType('bill');

        $manager->persist($commercialSheet);
        $manager->flush();
    }
    /**
     * Permet d'afficher la facture d'un document commercial (bill ou quote) pour impression
     * 
     * @Route("/commercial/sheet/{id}/print", name="commercial_sheet_print")
     *
     * @param CommercialSheet $commercialSheet
     * @return void
     */
    public function printBill(CommercialSheet $commercialSheet, InventoryRepository $inventoryRepo)
    {
        $inventories = $inventoryRepo->findAll();
        return $this->render('commercial_sheet/print_commercial_sheet.html.twig', [
            'commercialSheet' => $commercialSheet,
            'inventories'     => $inventories,
        ]);
    }

    /**
     * Gestion des commandes marquées livrée et/ou payée
     * 
     * @Route("/commercial/sheet/change/status", name="commercial_sheet_change_status")
     *
     * @param Request $request
     * @param CommercialSheetRepository $commercialSheetRepo
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    public function changeStatus(Request $request, CommercialSheetRepository $commercialSheetRepo, EntityManagerInterface $manager): JsonResponse
    {
        $paramJSON = $this->getJSONRequest($request->getContent());
        if (array_key_exists("commercialSheetDeliveredIds", $paramJSON) && array_key_exists("commercialSheetPaidIds", $paramJSON)) {
            if (!empty($paramJSON['commercialSheetDeliveredIds']) || !empty($paramJSON['commercialSheetPaidIds'])) {
                if (!empty($paramJSON['commercialSheetDeliveredIds'])) {
                    foreach ($paramJSON['commercialSheetDeliveredIds'] as $Id) {
                        $commercialSheet = $commercialSheetRepo->findOneBy(['id' => intval($Id)]);
                        dump($commercialSheet);
                        $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
                        $commercialSheet->setDeliveryStatus(true)
                            ->setDeliverAt($date);
                        if ($commercialSheet->getDeliveryStatus() && $commercialSheet->getPaymentStatus()) {
                            $commercialSheet->setCompletedStatus(true);
                            $commercialSheet->setCompletedAt($date);
                        }
                        $manager->persist($commercialSheet);
                    }
                }
                if (!empty($paramJSON['commercialSheetPaidIds'])) {
                    foreach ($paramJSON['commercialSheetPaidIds'] as $Id) {
                        $commercialSheet = $commercialSheetRepo->findOneBy(['id' => intval($Id)]);
                        $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
                        $commercialSheet->setPaymentStatus(true)
                            ->setPayAt($date);
                        if ($commercialSheet->getDeliveryStatus() && $commercialSheet->getPaymentStatus()) {
                            $commercialSheet->setCompletedStatus(true);
                            $commercialSheet->setCompletedAt($date);
                        }
                        $manager->persist($commercialSheet);
                    }
                }
                $manager->flush();
                return $this->json([
                    'code' => 200,
                    'Delivered' => $paramJSON['commercialSheetDeliveredIds'],
                    'Paid' => $paramJSON['commercialSheetPaidIds'],
                ], 200);
            }
        }
        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 200);
    }

    /**
     * Permet d'afficher la fiche journalière de livraison ou de sortie de stock
     * 
     * @Route("/journal/{journal<[a-z]+>}/{town<\d+>}", name="print_journal")
     *
     * @param [type] $journal
     * @param [type] $town
     * @param Request $request
     * @param CommercialSheetRepository $orderRepo
     * @return void
     */
    public function printJournal($journal, $town, Request $request, CommercialSheetRepository $commercialSheetRepo, InventoryRepository $inventoryRepo, ProductRepository $productRepo)
    {
        dump($request->request->get("commercialSheetIds"));
        $inventories = $inventoryRepo->findAll();
        //die();
        //$paramJSON = $this->getJSONRequest($request->getContent());
        $paramJSON = $request->request->get("commercialSheetIds");
        if (array_key_exists("commercialSheetIds", $paramJSON)) {
            if (!empty($paramJSON['commercialSheetIds'])) {
                $commercialSheets = [];
                foreach ($paramJSON['commercialSheetIds'] as $id) {
                    $orders[] = $commercialSheetRepo->findOneBy(['id' => intval($id)]);
                }
                dump($commercialSheets);
                if ($journal == 'delivery') {
                    return $this->render('commercial_sheet/printDeliveryJournal.html.twig', [
                        'commercialSheets' => $commercialSheets,
                        'town'             => $inventoryRepo->findOneBy(['id' => $town])->getName(),
                        'inventories'      => $inventories
                    ]);
                } else if ($journal == 'inventory') {
                    $products = $productRepo->findAll();
                    return $this->render('commercial_sheet/printInventoryExitJournal.html.twig', [
                        'commercialSheets'  => $commercialSheets,
                        'town'              => $inventoryRepo->findOneBy(['id' => $town])->getName(),
                        'products'          => $products,
                        'inventories'       => $inventories,
                    ]);
                }
            }
        }
        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 200);
    }
}
