<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use DateTimeZone;
use App\Entity\Product;
use App\Entity\Inventory;
use App\Entity\StockMovement;
use App\Entity\BusinessContact;
use App\Entity\CommercialSheet;
use App\Form\CommercialSheetType;
use App\Repository\LotRepository;
use App\Form\CommercialSheetItemType;
use App\Repository\ProductRepository;
use App\Entity\CommercialSheetItemLot;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use App\Repository\CommercialSheetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommercialSheetItemRepository;
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
     * @Route("/commercial/sheet/{type<[a-z]+>}/home", name="commercial_sheet_index")
     * @IsGranted("ROLE_USER")
     */
    public function index($type, EntityManagerInterface $manager)
    {
        //$inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //$commercialSheets = $commercialSheetRepo->findBy(['type' => $type]);
        $commercialSheets = [];
        $commercialSheets_ = $manager->createQuery("SELECT cms
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND e.id = :entId                                                              
                                        ")
            ->setParameters(array(
                'entId'   => $this->getUser()->getEnterprise()->getId(),
                'type_'   => $type,
            ))
            //->setMaxResults(10)
            ->getResult();
        //dump($commercialSheets_);
        foreach ($commercialSheets_ as $commercialSheet) {
            $commercialSheets[] = $commercialSheet;
        }
        return $this->render('commercial_sheet/index_commercial_sheet.html.twig', [
            'commercialSheets'      => $commercialSheets,
            //'inventories'           => $inventories,
            'type'                  => $type,
        ]);
    }


    /**
     * Permet de créer une commande (order)
     *
     * @Route("/commercial/sheet/new/{id<\d+>}/{type<[a-z]+>}/{stock<\d+>?0}", name = "commercial_sheet_create")
     * 
     * @Security( "is_granted('ROLE_USER') and user.getEnterprise().getIsActivated() == true " )
     * 
     * @return Response
     */
    public function create(BusinessContact $businessContact, $type, $stock, Request $request, InventoryRepository $inventoryRepo, LotRepository $lotRepo, EntityManagerInterface $manager, InventoryAvailabilityRepository $inventoryAvailabilityRepo, CommercialSheetItemRepository $commercialSheetItemRepo)
    { //
        $commercialSheet = new CommercialSheet();
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);

        //Variable déterminant le type d'abonnement du client Entreprise
        //$iconBillOnly  = true;
        $iconStock = true;
        //$iconCombination = true;
        $isActivated = $this->getUser()->getEnterprise()->getIsActivated();
        $productRefNumber = $this->getUser()->getEnterprise()->getSubscription()->getProductRefNumber();
        $sheetNumber = $this->getUser()->getEnterprise()->getSubscription()->getSheetNumber();
        //dump('Sheet Number = ' . $sheetNumber);
        //dump('Product Ref Number = ' . $productRefNumber);
        if (($productRefNumber == 0) || ($isActivated == false)) { //Si le nombre de référence est 0 alors subscription au module stock désactiver
            $iconStock = false;
        }

        $availabilities = [];
        $inventoryAvailabilities = [];
        $inventory_ = null;
        if ($businessContact) {
            $commercialSheet->setBusinessContact($businessContact)
                ->setUser($this->getUser())
                ->setType($type);
            if ($iconStock == true) {
                if ($type == 'bill') {
                    //Récupération de l'inventaire du client Entreprise
                    $inventory_ = $inventoryRepo->findOneBy(['id' => $stock, 'type' => 'PF']);
                    if ($inventory_) { //Si l'inventaire existe
                        $commercialSheet->setInventory($inventory_);
                        //dump($inventory_);
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
                } else if ($type == 'quote') {
                    $commercialSheet->setInventory($inventory_);
                    //$inventory_ = $inventoryRepo->findOneBy(['id' => $stock]);
                }
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
        $form = $this->createForm(CommercialSheetType::class, $commercialSheet, [
            'entId' => $this->getUser()->getEnterprise()->getId(),
            'isEdit' => false,
        ]);
        $form->handleRequest($request);

        //dump($this->getUser()->getEnterprise()->getSubscription());
        $commercialSheetItemErrorFlag = false;
        $message = "<ul>";
        if ($form->isSubmitted() && $form->isValid()) {
            $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
            //dump($commercialSheet->getCommercialSheetItems());
            foreach ($commercialSheet->getCommercialSheetItems() as $commercialSheetItem) {
                //dump($commercialSheetItem);
                if ($commercialSheetItem->getQuantity() >= 1) { //On considère uniquement les items de Qty >= 1
                    //Je vérifie si l'item est déjà existant en BDD pour éviter les doublons 

                    if ($commercialSheetItem->getItemOfferType() == 'hasStock') { //Gestion des items avec des Products en stock 
                        //dump($type);
                        if ($type === 'bill') {
                            $commercialSheetItem_ = $commercialSheetItemRepo->findOneBy([
                                'designation' => $commercialSheetItem->getDesignation(),
                                'pu' => $commercialSheetItem->getPU(),
                                'quantity' => $commercialSheetItem->getQuantity(),
                                'remise'   => $commercialSheetItem->getRemise()
                            ]);
                            //dump($commercialSheetItem->getAvailable());
                            if (empty($commercialSheetItem_)) {
                                $commercialSheetItem->addCommercialSheet($commercialSheet);
                                //$manager->persist($commercialSheetItem);
                                //dump('commercialSheetItem dont exists ');
                            } else {
                                //dump('commercialSheetItem exists with id = ' . $commercialSheetItem_->getId());
                                //$commercialSheetItem = $commercialSheetItem_;
                                $commercialSheetItem_->setRemise($commercialSheetItem->getRemise())
                                    ->setAvailable($commercialSheetItem->getAvailable())
                                    ->setItemOfferType($commercialSheetItem->getItemOfferType())
                                    ->addCommercialSheet($commercialSheet);
                                $commercialSheet->addCommercialSheetItem($commercialSheetItem_);
                                $commercialSheet->removeCommercialSheetItem($commercialSheetItem);
                                $commercialSheetItem = $commercialSheetItem_;
                            }
                            //dd($iconStock);
                            if ($iconStock == true) { //Si le client Entreprise à souscrit à un module KnD Stock
                                //dump($commercialSheetItem->getProduct());
                                //Recherche de la disponibilité du produit contenu dans l'item dans l'inventaire passé en paramètre
                                $inventoryAvailability = $inventoryAvailabilityRepo->findOneBy(['inventory' => $inventory_, 'product' => $commercialSheetItem->getProduct()]);
                                //dump($inventoryAvailability);
                                if ($inventoryAvailability) { //Si cette disponibilité existe la mettre à jour ainsi que la quantité des lots relatifs à ce produit
                                    if ($commercialSheetItem->getQuantity() <= $inventoryAvailability->getAvailable()) {

                                        //Recherche des lots relatifs à ce produit dans l'inventaire reçu ordonné suivant le mode de management 
                                        //de ce dernier
                                        $order_ = $inventory_->getManagementMode() == 'FIFO' ? 'asc' : 'desc';
                                        //dump($commercialSheetItem->getProduct());
                                        $lots = $lotRepo->findBy(['inventory' => $inventory_, 'product' => $commercialSheetItem->getProduct()], ['dlc' => $order_]);
                                        //dump($lots);
                                        if (!empty($lots)) {
                                            $qtyToRemove = $commercialSheetItem->getQuantity();
                                            foreach ($lots as $lot) {
                                                if ($lot->getQuantity() > 0) {
                                                    //On vérifie si la lot n'est pas arrivé à date d'expiration
                                                    //$nowDate = new DateTime("now");
                                                    //$this->periodofvalidity = new DateTime($lot->getDlc()->format('Y/m/d'));
                                                    //$this->periodofvalidity->add(new DateInterval('P' . $this->duration . 'D'));
                                                    //$interval = $nowDate->diff($this->periodofvalidity);
                                                    //$valid = !$interval->invert;

                                                    //if (!$interval->invert) { // Si le lot est encore consommable
                                                    if ($lot->getAlert()) { // Si le lot est encore consommable
                                                        $diff = $lot->getQuantity() - $qtyToRemove;
                                                        //dump($lot);
                                                        //dump('diff = ' . $diff);
                                                        if ($diff >= 0) {
                                                            //$qty = $diff == 0 ? $qtyToRemove : $diff;
                                                            $lot->setQuantity($diff);
                                                            //dump($lot);

                                                            //Gestion du mouvement de stock
                                                            $stockMovement = new StockMovement();
                                                            $stockMovement->setCreatedAt($date)
                                                                ->setLot($lot)
                                                                ->setQuantity($qtyToRemove)
                                                                ->setType('Sale Exit')
                                                                ->setCommercialSheet($commercialSheet);
                                                            //dump($stockMovement);
                                                            $manager->persist($stockMovement);

                                                            $cmsiLot = new CommercialSheetItemLot();
                                                            $cmsiLot->setLot($lot)
                                                                ->setCommercialSheetItem($commercialSheetItem)
                                                                ->setQuantity($qtyToRemove);

                                                            $lot->addCommercialSheetItemLot($cmsiLot);

                                                            $commercialSheetItem->addCommercialSheetItemLot($cmsiLot)
                                                                //->addCommercialSheet($commercialSheet)
                                                                //->addCommercialSheet($commercialSheet)
                                                            ;

                                                            $manager->persist($cmsiLot);
                                                            //$manager->persist($commercialSheetItem);
                                                            $manager->persist($lot); //Sauvegarde du lot en BDD

                                                            $qtyToRemove = 0;
                                                            break;
                                                        } else {
                                                            $qtyToRemove = $qtyToRemove - $lot->getQuantity();

                                                            //Gestion du mouvement de stock
                                                            $stockMovement = new StockMovement();
                                                            $stockMovement->setCreatedAt($date)
                                                                ->setLot($lot)
                                                                ->setQuantity($lot->getQuantity())
                                                                ->setType('Sale Exit')
                                                                ->setCommercialSheet($commercialSheet);
                                                            //dump($stockMovement);
                                                            $manager->persist($stockMovement);

                                                            $cmsiLot = new CommercialSheetItemLot();
                                                            $cmsiLot->setLot($lot)
                                                                ->setCommercialSheetItem($commercialSheetItem)
                                                                ->setQuantity($lot->getQuantity());
                                                            $lot->addCommercialSheetItemLot($cmsiLot);

                                                            $commercialSheetItem->addCommercialSheetItemLot($cmsiLot);

                                                            $manager->persist($cmsiLot);
                                                            //$manager->persist($commercialSheetItem);
                                                            $lot->setQuantity(0);
                                                            $manager->persist($lot); //Sauvegarde du lot en BDD
                                                        }
                                                    }
                                                }
                                            }

                                            if ($qtyToRemove > 0) { //Si la quantité du produit est > 0 alors la commande n'est pas valide
                                                $commercialSheetItemErrorFlag = true;
                                                $message = $message . "<li>the quantity ({$commercialSheetItem->getQuantity()}) requested for the product({$commercialSheetItem->getProduct()->getName()}) cannot be deducted from a consumable lot</li>";
                                                //dump('commercialSheetItemErrorFlag = ' . $commercialSheetItemErrorFlag);
                                            } else if ($qtyToRemove === 0) { //Sinon mettre à jour la disponibilité en stock de ce produit
                                                //dump($inventoryAvailability);
                                                $inventoryAvailability->setAvailable($commercialSheetItem->getAvailable());
                                                //dump($inventoryAvailability);
                                                $manager->persist($inventoryAvailability);
                                            }
                                        }
                                        $manager->persist($commercialSheetItem);
                                    } else {
                                        $commercialSheetItemErrorFlag = true;
                                        $message = $message . "<li>the quantity ({$commercialSheetItem->getQuantity()}) requested for the product({$commercialSheetItem->getProduct()->getName()}) is greater than the availability ({$inventoryAvailability->getAvailable()})</li>";
                                    }

                                    /*foreach ($inventoryAvailabilities as $inventoryAvailability) {
                                        if ($commercialSheetItem->getProduct()->getId() == $inventoryAvailability->getProduct()->getId()) {
                                            $inventoryAvailability->setAvailable($commercialSheetItem->getAvailable());
                                            $manager->persist($inventoryAvailability);
                                        }
                                        $availabilities['' . $productId] = $inventoryAvailability->getAvailable();
                                        break;
                                    }*/
                                }
                            }
                        } else if ($type === 'quote') {
                            $commercialSheetItem->addCommercialSheet($commercialSheet);
                        }
                        //$manager->persist($commercialSheetItem);
                    } else {
                        $commercialSheetItem_ = $commercialSheetItemRepo->findOneBy([
                            'designation' => $commercialSheetItem->getDesignation(),
                            'pu' => $commercialSheetItem->getPU(),
                            'quantity' => $commercialSheetItem->getQuantity(),
                            'remise'   => $commercialSheetItem->getRemise()
                        ]);
                        //dd($commercialSheetItem->getAvailable());
                        if (empty($commercialSheetItem_)) {
                            $commercialSheetItem->setProduct(null)
                                ->addCommercialSheet($commercialSheet);
                            //$manager->persist($commercialSheetItem);
                            //dump('commercialSheetItem dont exists ');
                        } else {
                            //dump('commercialSheetItem exists with id = ' . $commercialSheetItem_->getId());
                            //$commercialSheetItem = $commercialSheetItem_;
                            $commercialSheetItem_->setRemise($commercialSheetItem->getRemise())
                                ->setItemOfferType($commercialSheetItem->getItemOfferType())
                                ->addCommercialSheet($commercialSheet);
                            $commercialSheet->addCommercialSheetItem($commercialSheetItem_);
                            $commercialSheet->removeCommercialSheetItem($commercialSheetItem);
                            $commercialSheetItem = $commercialSheetItem_;
                        }


                        $manager->persist($commercialSheetItem);
                        //$commercialSheetItem->setProduct($service); 
                        //dump($commercialSheetItem);
                    }
                } else {
                    $commercialSheet->removeCommercialSheetItem($commercialSheetItem);
                }
            }
            //die();
            // dump($commercialSheet->getCommercialSheetItems());
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

            /*foreach ($commercialSheet->getReductions() as $reduction) {
                dump($reduction);
                $reduction->addOrder($commercialSheet);
                $manager->persist($reduction);
            }*/

            //die();
            //dump(date('Y-m'));
            if ($sheetNumber && ($isActivated == true)) { //Si l'un des abonnements KnB Bill est activé pour le client Entreprise actuel
                //Récupération de tous les documents de l'entreprise générés pendant le mois en cours
                /*JOIN cms.businessContact bc   
                JOIN bc.enterprise ent
                AND ent.id = :entId*/
                $sheets = $manager->createQuery("SELECT COUNT(cms.createdAt) AS CreatedDate
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u
                                            JOIN u.enterprise e 
                                            WHERE cms.createdAt LIKE :nowDate 
                                            AND e.id = :entId                                                                   
                                            ")
                    ->setParameters(array(
                        'nowDate' => '%' . date('Y-m') . '%',
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                    ))
                    ->getResult();

                //dump($sheetNumber);
                //dd($sheets[0]['CreatedDate']);
                $totalSheet = intval($sheets[0]['CreatedDate']);
                //dd($totalSheet);
                //die();
                if ($totalSheet < $sheetNumber) { //Si la limite mensuel de document n'est pas encore atteinte

                    if (!$commercialSheetItemErrorFlag) { //Si la commande est valide 
                        //dump('$commercialSheetItemErrorFlag = ' . $commercialSheetItemErrorFlag);
                        //$commercialSheetItem->addCommercialSheet($commercialSheet);
                        //$manager->persist($commercialSheetItem);
                        $manager->persist($commercialSheet);
                        $manager->flush();

                        $this->addFlash(
                            'success',
                            "The {$commercialSheet->getType()} has been registered successfully !"
                        );

                        return $this->redirectToRoute('commercial_sheet_print', [
                            'inventories' => $inventories,
                            'id' => $commercialSheet->getId(),
                        ]);
                    } else {
                        $message = $message . "</ul>";
                        //dd($message);
                        $this->addFlash(
                            'danger',
                            "The backup of the {$commercialSheet->getType()} failed because <p>" . $message . "</p>"
                        );
                    }
                } else { //Sinon redirection vers la page des partenaires d'affaire
                    $this->addFlash(
                        'danger',
                        "The backup of the {$commercialSheet->getType()} failed because you have already reached the monthly document limit to generate"
                    );

                    $this->redirectToRoute("business_contacts_index", ['type' => 'customer']);
                }
            } else { //Sinon redirection vers la page de réabonnement
                $message = $message . "</ul>";
                $this->addFlash(
                    'danger',
                    "The backup of the {$commercialSheet->getType()} failed because <p>" . $message . "</p>"
                );

                //++++++++++
            }
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
     * Permet d'afficher le formulaire d'édition d'une commande (commercialSheet)
     *
     * @Route("/commercial/sheet/{id<\d+>}/edit", name="commercial_sheet_edit")
     * 
     * @Security( "is_granted('ROLE_USER') and commercialSheet.getUser().getEnterprise() === user.getEnterprise() " )
     * 
     * @return Response
     */
    public function edit(CommercialSheet $commercialSheet, Request $request, EntityManagerInterface $manager, InventoryRepository $inventoryRepo, InventoryAvailabilityRepository $inventoryAvailabilityRepo)
    { //

        //dump($commercialSheet);
        //Variable déterminant le type d'abonnement du client Entreprise
        //$iconBillOnly  = true;
        // $iconStock = true;
        // //$iconCombination = true;
        // $productRefNumber = $this->getUser()->getEnterprise()->getSubscription()->getProductRefNumber();
        // $sheetNumber = $this->getUser()->getEnterprise()->getSubscription()->getSheetNumber();
        // //dump('Sheet Number = ' . $sheetNumber);
        // //dump('Product Ref Number = ' . $productRefNumber);
        // if ($productRefNumber == 0) { //Si le nombre de référence est 0 alors subscription au module stock désactiver
        //     $iconStock = false;
        // }

        $availabilities = [];
        $inventoryAvailabilities = [];
        $inventory_ = null;

        if ($commercialSheet->getType() == 'bill') {
            //Récupération de l'inventaire du client Entreprise
            $inventory_ = $inventoryRepo->findOneBy(['id' => $commercialSheet->getInventory()->getId(), 'type' => 'PF']);
            if ($inventory_) { //Si l'inventaire existe
                //$lots = $inventory_->getLots();
                //Recherche des disponibilités relatif à l'inventaire associé à la facture
                //foreach ($inventories as $inventory) {
                //if ($inventory == $inventory_) {
                $inventoryAvailabilities = $inventoryAvailabilityRepo->findBy(['inventory' => $commercialSheet->getInventory()]);
                //break;
                //}
                //}

                foreach ($inventoryAvailabilities as $inventoryAvailability) {
                    $productId = $inventoryAvailability->getProduct()->getId();
                    $availabilities['' . $productId] = $inventoryAvailability->getAvailable();
                }
            } else {
                //Exception de l'erreur Inventory non défini pour ce document
            }
        } else if ($commercialSheet->getType() == 'quote') {
            $commercialSheet->setInventory($inventory_);
            //$inventory_ = $inventoryRepo->findOneBy(['id' => $stock]);
        }


        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        //dump($inventory_);
        /*$choices = [];
        foreach ($inventories as $inventory) {
            $choices['' . strtoupper($inventory)] = ucfirst($inventory);
        }*/

        //  instancier un form externe
        $form = $this->createForm(CommercialSheetType::class, $commercialSheet, [
            'entId'  => $this->getUser()->getEnterprise()->getId(),
            'isEdit' => true,
        ]);

        $form->handleRequest($request);

        //dump($this->getUser()->getEnterprise()->getSubscription());
        $commercialSheetItemErrorFlag = false;
        $message = "<ul>";


        if ($form->isSubmitted() && $form->isValid()) {
            //dump($commercialSheet);
            $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
            //foreach ($commercialSheet->getCommercialSheetItems() as $commercialSheetItem) {
            //if ($commercialSheetItem->getQuantity() >= 1) { //On considère uniquement les items de Qty >= 1
            // dd($commercialSheetItem->getIsChanged());
            // if ($commercialSheetItem->getIsChanged()) {
            /*if ($commercialSheetItem->getItemOfferType() == 'hasStock') { //Gestion des items avec des Products en stock 
                            //dump($type);
                            if ($commercialSheet->getType() == 'bill') {
                                //dump($iconStock);

                                //dump($commercialSheetItem->getItemOfferType());
                                //Recherche de la disponibilité du produit contenu dans l'item dans l'inventaire passé en paramètre
                                $inventoryAvailability = $inventoryAvailabilityRepo->findOneBy(['inventory' => $inventory_, 'product' => $commercialSheetItem->getProduct()]);
                                //dump($inventoryAvailability);
                                if ($inventoryAvailability) { //Si cette disponibilité existe la mettre à jour ainsi que la quantité des lots relatifs à ce produit
                                    if ($commercialSheetItem->getQuantity() <= $inventoryAvailability->getAvailable()) {

                                        //Recherche des lots relatifs à ce produit dans l'inventaire reçu ordonné suivant le mode de management 
                                        //de ce dernier
                                        $order_ = $inventory_->getManagementMode() == 'FIFO' ? 'asc' : 'desc';
                                        //dump($commercialSheetItem->getProduct());
                                        $lotRepo = $manager->getRepository('App:Lot');
                                        $lots = $lotRepo->findBy(['inventory' => $inventory_, 'product' => $commercialSheetItem->getProduct()], ['dlc' => $order_]);
                                        if (!empty($lots)) {
                                            $qtyToRemove = $commercialSheetItem->getQuantity();
                                            foreach ($lots as $lot) {
                                                if ($lot->getQuantity() > 0) {
                                                    //On vérifie si la lot n'est pas arrivé à date d'expiration
                                                    $nowDate = new DateTime("now");
                                                    $this->periodofvalidity = new DateTime($lot->getDlc()->format('Y/m/d'));
                                                    //$this->periodofvalidity->add(new DateInterval('P' . $this->duration . 'D'));
                                                    $interval = $nowDate->diff($this->periodofvalidity);
                                                    //$valid = !$interval->invert;

                                                    if (!$interval->invert) { // Si le lot est encore consommable
                                                        $diff = $lot->getQuantity() - $qtyToRemove;
                                                        //dump('diff = ' . $diff);
                                                        if ($diff >= 0) {
                                                            //$qty = $diff == 0 ? $qtyToRemove : $diff;
                                                            $lot->setQuantity($diff);
                                                            $manager->persist($lot); //Sauvegarde du lot en BDD

                                                            // dump($lot);
                                                            //Gestion du mouvement de stock
                                                            $stockMovement = new StockMovement();
                                                            $stockMovement->setCreatedAt($date)
                                                                ->setLot($lot)
                                                                ->setQuantity($qtyToRemove)
                                                                ->setType('Sale Exit')
                                                                ->setCommercialSheet($commercialSheet);
                                                            //dump($stockMovement);
                                                            $manager->persist($stockMovement);
                                                            $qtyToRemove = 0;
                                                            break;
                                                        } else {
                                                            $qtyToRemove = $qtyToRemove - $lot->getQuantity();

                                                            //Gestion du mouvement de stock
                                                            $stockMovement = new StockMovement();
                                                            $stockMovement->setCreatedAt($date)
                                                                ->setLot($lot)
                                                                ->setQuantity($lot->getQuantity())
                                                                ->setType('Sale Exit')
                                                                ->setCommercialSheet($commercialSheet);
                                                            //dump($stockMovement);
                                                            $manager->persist($stockMovement);

                                                            $lot->setQuantity(0);
                                                            $manager->persist($lot); //Sauvegarde du lot en BDD
                                                        }
                                                    }
                                                }
                                            }

                                            if ($qtyToRemove > 0) { //Si la quantité du produit est > 0 alors la commande n'est pas valide
                                                $commercialSheetItemErrorFlag = true;
                                                $message = $message . "<li>the quantity {$commercialSheetItem->getQuantity()}) requested for the product({$commercialSheetItem->getProduct()->getName()} is greater than the availability ({$inventoryAvailability->getAvailable()})</li>";
                                            } else { //Sinon mettre à jour la disponibilité en stock de ce produit
                                                $inventoryAvailability->setAvailable($commercialSheetItem->getAvailable());
                                                $manager->persist($inventoryAvailability);
                                            }
                                        }
                                    } else {
                                        $commercialSheetItemErrorFlag = true;
                                        $message = $message . "<li>the quantity {$commercialSheetItem->getQuantity()}) requested for the product({$commercialSheetItem->getProduct()->getName()} is greater than the availability ({$inventoryAvailability->getAvailable()})</li>";
                                    }

                                    // foreach ($inventoryAvailabilities as $inventoryAvailability) {
                                    //         if ($commercialSheetItem->getProduct()->getId() == $inventoryAvailability->getProduct()->getId()) {
                                    //             $inventoryAvailability->setAvailable($commercialSheetItem->getAvailable());
                                    //             $manager->persist($inventoryAvailability);
                                    //         }
                                    //         $availabilities['' . $productId] = $inventoryAvailability->getAvailable();
                                    //         break;
                                    //     }
                                }
                            }
                            //$manager->persist($commercialSheetItem);
                        } else {
                            $commercialSheetItem->setProduct(null);
                        }
                    }*/

            //Je vérifie si l'item est déjà existant en BDD pour éviter les doublons 
            /*$commercialSheetItemRepo = $manager->getRepository('App:CommercialSheetItem');
                    $commercialSheetItem_ = $commercialSheetItemRepo->findOneBy([
                        'designation' => $commercialSheetItem->getDesignation(),
                        'pu' => $commercialSheetItem->getPU(),
                        'quantity' => $commercialSheetItem->getQuantity()
                    ]);

                    if (empty($commercialSheetItem_)) {
                        $commercialSheetItem->addCommercialSheet($commercialSheet);
                        $manager->persist($commercialSheetItem);
                        // dump('commercialSheetItem dont exists ');
                    } else {
                        //dump('commercialSheetItem exists with id = ' . $commercialSheetItem_->getId());
                        //$commercialSheetItem = $commercialSheetItem_;
                        $commercialSheetItem_->addCommercialSheet($commercialSheet);
                        $commercialSheet->addCommercialSheetItem($commercialSheetItem_);
                        $commercialSheet->removeCommercialSheetItem($commercialSheetItem);
                    }*/
            //$commercialSheetItem->setProduct($service); 
            //dump($commercialSheetItem);
            //}
            //}
            //die();
            // dump($commercialSheet->getCommercialSheetItems());
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

            if (!$commercialSheetItemErrorFlag) { //Si la commande est valide 
                //die();
                $manager->persist($commercialSheet);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "The {$commercialSheet->getType()} has been registered successfully !"
                );

                return $this->redirectToRoute('commercial_sheet_print', [
                    'id' => $commercialSheet->getId(),
                ]);
            } else {
                $message = $message . "</ul>";
                $this->addFlash(
                    'success',
                    "The backup of the {$commercialSheet->getType()} failed because <p>" . $message . "</p>"
                );
            }
        }

        return $this->render(
            'commercial_sheet/edit.html.twig',
            [
                'form'            => $form->createView(),
                'commercialSheet' => $commercialSheet,
                'availabilities'  => $availabilities,

            ]
        );
    }

    /**
     * Permet de supprimer une commande (order)
     * 
     * @Route("/commercial/sheet/{id}/delete", name="commercial_sheet_delete")
     * 
     * @Security( "is_granted('ROLE_USER') and commercialSheet.getUser().getEnterprise() === user.getEnterprise() " )
     *
     * @param CommercialSheet $commercialSheet
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(CommercialSheet $commercialSheet, EntityManagerInterface $manager)
    {
        $businessContact = $manager->getRepository('App:BusinessContact')->findOneBy(['id' => $commercialSheet->getBusinessContact()->getId()]);
        //dump($businessContact);
        foreach ($commercialSheet->getCommercialSheetItems() as $commercialSheetItem) {
            $cmsiLots = $manager->getRepository('App:CommercialSheetItemLot')->findBy(['commercialSheetItem' => $commercialSheetItem]);
            //dump($cmsiLots);
            foreach ($cmsiLots as $cmsiLot) {
                $lot = $cmsiLot->getLot();
                //dump($lot);
                $inventoryAvailability = $manager->getRepository('App:InventoryAvailability')->findOneBy(['inventory' => $lot->getInventory(), 'product' => $lot->getProduct()]);
                if ($inventoryAvailability) {
                    //dump($inventoryAvailability);
                    $tmp = $inventoryAvailability->getAvailable() + $cmsiLot->getQuantity();
                    $inventoryAvailability->setAvailable($tmp);
                    //dump($inventoryAvailability);

                    $manager->persist($inventoryAvailability);
                }
                $qty = $lot->getQuantity() + $cmsiLot->getQuantity();
                $lot->setQuantity($qty);
                //dump($lot);
                $manager->persist($lot);
            }
            $commercialSheetItem->removeCommercialSheet($commercialSheet);
            //dump($commercialSheetItem);
            $manager->persist($commercialSheetItem);
        }
        $businessContact->removeCommercialSheet($commercialSheet);
        $manager->persist($businessContact);
        //die();
        $manager->remove($commercialSheet);
        $manager->flush();

        $this->addFlash(
            'success',
            "The removal of {$commercialSheet->getType()} ID #<strong>{$commercialSheet->getNumber()} </strong> has been completed successfully !"
        );

        return $this->redirectToRoute("commercial_sheet_index", ['type' => $commercialSheet->getType()]);
    }

    /**
     * Permet de convertir un devis(quote) en facture(bill)
     * 
     * @Route("/commercial/sheet/{id<\d+>}/convert", name="commercial_sheet_convert")
     *
     * @Security( "is_granted('ROLE_USER') and commercialSheet.getUser().getEnterprise() === user.getEnterprise() " )
     * 
     * @param CommercialSheet $commercialSheet
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function convert(CommercialSheet $commercialSheet, EntityManagerInterface $manager, InventoryAvailabilityRepository $inventoryAvailabilityRepo)
    {
        $iconStock = true;
        $isActivated = $this->getUser()->getEnterprise()->getIsActivated();
        $productRefNumber = $this->getUser()->getEnterprise()->getSubscription()->getProductRefNumber();
        $sheetNumber = $this->getUser()->getEnterprise()->getSubscription()->getSheetNumber();
        //dump('Sheet Number = ' . $sheetNumber);
        //dump('Product Ref Number = ' . $productRefNumber);
        if (!$productRefNumber || ($isActivated == false)) { //Si le nombre de référence est 0 alors subscription au module stock désactiver
            $iconStock = false;
        }
        $convertFlag = true;
        $message = "<ul>";

        if ($iconStock == true) {
            //Vérification des disponibilités en stock des produits contenus dans le doc
            foreach ($commercialSheet->getCommercialSheetItems() as $commercialSheetItem) {
                /*if ($commercialSheetItem->getOfferType() == 'Product') {
                    $inventoryAvailability = $inventoryAvailabilityRepo->findOneBy(['inventory' => $commercialSheet->getInventory(), 'product' => $commercialSheetItem->getProduct()]);
                    if ($inventoryAvailability->getAvailable() >= $commercialSheetItem->getQuantity()) {
                        $qty = $inventoryAvailability->getAvailable() - $commercialSheetItem->getQuantity();
                        $inventoryAvailability->getAvailable($qty);

                        $manager->persist($inventoryAvailability);

                        //Mouvement de Stock : sortie de vente

                    } else {
                        $convertFlag = false;
                        $message = $message . "<li>The demand quantity({$commercialSheetItem->getQuantity()}) of product {$commercialSheetItem->getProduct()->getName()} is greater than the availability ({$inventoryAvailability->getAvailable()})</li>";
                    }
                }*/

                if ($commercialSheetItem->getItemOfferType() == 'hasStock') { //Gestion des items avec des Products en stock 
                    //dump($type);
                    //dd($iconStock);
                    if ($iconStock == true) { //Si le client Entreprise à souscrit à un module KnD Stock
                        //dump($commercialSheetItem->getProduct());
                        //Recherche de la disponibilité du produit contenu dans l'item dans l'inventaire passé en paramètre
                        $inventoryAvailability = $inventoryAvailabilityRepo->findOneBy(['inventory' => $commercialSheet->getInventory(), 'product' => $commercialSheetItem->getProduct()]);
                        //dump($inventoryAvailability);
                        if ($inventoryAvailability) { //Si cette disponibilité existe la mettre à jour ainsi que la quantité des lots relatifs à ce produit
                            if ($commercialSheetItem->getQuantity() <= $inventoryAvailability->getAvailable()) {
                                $inventoryAvailability->setAvailable($commercialSheetItem->getAvailable());
                                //dump($inventoryAvailability);
                                $manager->persist($inventoryAvailability);
                                $manager->persist($commercialSheetItem);
                            } else {
                                $convertFlag = false;
                                $message = $message . "<li>The demand quantity({$commercialSheetItem->getQuantity()}) of product {$commercialSheetItem->getProduct()->getName()} is greater than the availability ({$inventoryAvailability->getAvailable()})</li>";
                            }
                        }
                    }

                    //$manager->persist($commercialSheetItem);
                }
            }
        }

        if ($convertFlag == true) {
            $commercialSheet->setType('bill');

            $manager->persist($commercialSheet);
            $manager->flush();

            $this->addFlash(
                'success',
                "The conversion of the {$commercialSheet->getType()} ID #<strong>{$commercialSheet->getNumber()} </strong> into a Bill has been successfully completed !"
            );
        } else {
            $message = $message . "</ul>";
            $this->addFlash(
                'success',
                "The conversion of the {$commercialSheet->getType()} ID #<strong>{$commercialSheet->getNumber()} </strong> into a Bill failed because <p>" . $message . "</p>"
            );
        }

        return $this->redirectToRoute("commercial_sheet_index", ['type' => $commercialSheet->getType()]);
    }

    /**
     * Permet d'afficher la facture d'un document commercial (bill ou quote) pour impression
     * 
     * @Route("/commercial/sheet/{id}/print", name="commercial_sheet_print")
     * 
     * @Security( "is_granted('ROLE_USER') and commercialSheet.getUser().getEnterprise() === user.getEnterprise() " )
     *
     * @param CommercialSheet $commercialSheet
     * @return void
     */
    public function printBill(CommercialSheet $commercialSheet, EntityManagerInterface $manager)
    {
        //$inventories = $inventoryRepo->findAll();
        //$date = $commercialSheet->getCreatedAt();
        $cms = $manager->createQuery("SELECT cms
                                     FROM App\Entity\CommercialSheet cms
                                     JOIN cms.user u
                                     JOIN u.enterprise e
                                     WHERE cms.createdAt LIKE :dat
                                     AND e.id = :entId

                                    ")
            ->setParameters([
                'dat' => '%' . $commercialSheet->getCreatedAt()->format('Y-m') . '%',
                'entId' => $this->getUser()->getEnterprise()->getId(),
            ])
            ->getResult();
        $numOrder = 0;
        $str = '';
        foreach ($cms as $key => $value) {
            if ($value === $commercialSheet) $numOrder = $key + 1;
        }
        //dump($numOrder);
        //Check if the variable $numOrder is an integer:
        if (!filter_var($numOrder, FILTER_VALIDATE_INT)) {
            //echo "Only integer values are required!";
            //exit();
        } else {
            // Convert the integer to array
            $int_array  = array_map('intval', str_split($numOrder));
            //get the lenght of the array
            $int_lenght = count($int_array);
        }
        //Check to make sure the lenght of the int does not exceed or less than10
        if ($int_lenght < 4) {
            $rest  = 4 - $int_lenght;
            for ($i = 0; $i < $rest; $i++) {
                $str .= '0';
            }
            //echo "Only 10 digit numbers are allow ! nbdigit = " . $int_lenght;
            //exit();
        } else {
            //echo $numOrder . " is an integer and its lenght is exactly " . $int_lenght;
            //Then proceed with your code
        }
        $str .= $numOrder;

        return $this->render('commercial_sheet/print_commercial_sheet.html.twig', [
            'commercialSheet' => $commercialSheet,
            'numOrder'        => $str,
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
                        //dump($commercialSheet);
                        if ($commercialSheet->getUser()->getEnterprise() === $this->getUser()->getEnterprise()) {
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
                }
                if (!empty($paramJSON['commercialSheetPaidIds'])) {
                    foreach ($paramJSON['commercialSheetPaidIds'] as $Id) {
                        $commercialSheet = $commercialSheetRepo->findOneBy(['id' => intval($Id)]);
                        $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
                        $commercialSheet->setPaymentStatus(true)
                            ->setPayAt($date)
                            ->setAdvancePayment($commercialSheet->getAmountNetToPaid());

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
     * @Route("/journal/{journal<[a-z]+>}", name="print_journal")
     *
     * @param [type] $journal
     * @param [type] $town
     * @param Request $request
     * @param CommercialSheetRepository $orderRepo
     * @return void
     */
    public function printJournal($journal, Request $request, CommercialSheetRepository $commercialSheetRepo, InventoryRepository $inventoryRepo, ProductRepository $productRepo)
    {
        //dump($request->request->get("commercialSheetIds"));
        $inventories = $inventoryRepo->findAll();
        //die();
        //$paramJSON = $this->getJSONRequest($request->getContent());
        $paramJSON = $request->request->get("commercialSheetIds");
        if (array_key_exists("commercialSheetIds", $paramJSON)) {
            if (!empty($paramJSON['commercialSheetIds'])) {
                $commercialSheets = [];
                foreach ($paramJSON['commercialSheetIds'] as $id) {
                    $commercialSheet = $commercialSheetRepo->findOneBy(['id' => intval($id)]);
                    if ($commercialSheet->getUser()->getEnterprise() === $this->getUser()->getEnterprise()) $commercialSheets[] = $commercialSheet;
                }
                //dump($commercialSheets);
                if ($journal == 'delivery') {
                    return $this->render('commercial_sheet/printDeliveryJournal.html.twig', [
                        'commercialSheets' => $commercialSheets,
                        //'town'             => $inventoryRepo->findOneBy(['id' => $town])->getName(),
                        'inventories'      => $inventories
                    ]);
                } else if ($journal == 'inventory') {
                    $products = $productRepo->findAll();
                    return $this->render('commercial_sheet/printInventoryExitJournal.html.twig', [
                        'commercialSheets'  => $commercialSheets,
                        //'town'              => $inventoryRepo->findOneBy(['id' => $town])->getName(),
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
