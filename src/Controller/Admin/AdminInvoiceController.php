<?php

namespace App\Controller\Admin;

use DateTime;
use DateTimeZone;
use App\Entity\CommercialSheet;
use App\Entity\CommercialSheetItem;
use App\Form\AdminSubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminInvoiceController extends ApplicationController
{
    /**
     * @Route("/admin/invoices/{type<[a-z]+>}", name="admin_invoices")
     */
    public function index($type, EntityManagerInterface $manager)
    {
        $invoices = [];
        $enterprises = $manager->getRepository('App:Enterprise')->findAll();
        if ($type === 'all') {
            $invoices_ = $manager->createQuery("SELECT cms
                                                FROM App\Entity\CommercialSheet cms
                                                JOIN cms.user u
                                                JOIN u.enterprise e
                                                WHERE cms.type = 'bill'
                                                AND e.id = :entId                                                              
                                            ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),

                ))
                //->setMaxResults(10)
                ->getResult();
            //dump($commercialSheets_);
            foreach ($invoices_ as $invoice) {
                $invoices[] = $invoice;
            }
        }
        $tarifs = null;
        $subscriptions = $manager->getRepository('App:Subscription')->findAll();
        foreach ($subscriptions as $subscription) {
            $tarifs['' . $subscription->getId()] = $subscription->getTarifs();
        }

        $form = $this->createForm(AdminSubscriptionType::class, $subscription);

        //dump($enterprises);
        return $this->render('admin/invoices/index_invoices.html.twig', [
            'invoices'    => $invoices,
            'enterprises' => $enterprises,
            'type'        => $type,
            'form'        => $form->createView(),
            'tarifs'      => $tarifs,
        ]);
    }

    /**
     * Permet d'enregistrer une nouvelle commande d'abonnement
     * 
     * @Route("/order/subscription", name="order_subscription")
     *
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_ADMIN')" )
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function orderASubscription(Request $request, EntityManagerInterface $manager)
    {
        $paramJSON = $this->getJSONRequest($request->getContent());
        if ((array_key_exists("subscriptionName", $paramJSON) && !empty($paramJSON['subscriptionName'])) && (array_key_exists("duration", $paramJSON) && !empty($paramJSON['duration'])) && (array_key_exists("price", $paramJSON) && !empty($paramJSON['price'])) && (array_key_exists("ent", $paramJSON) && !empty($paramJSON['ent']))) {
            $enterprise = $manager->getRepository('App:Enterprise')->findOneBy(['id' => $paramJSON['ent']]);
            if ($enterprise) {
                //Création d'une nouvelle facture
                $commercialSheet = new CommercialSheet();
                $commercialSheet->setEnterprise($enterprise)
                    ->setUser($this->getUser())
                    ->setDuration($paramJSON['duration'])
                    ->setFixReduction(0)
                    ->setAdvancePayment(0)
                    ->setType('bill');

                //Création de l'article Abonnement pour la facture 
                $commercialSheetItem = new CommercialSheetItem();
                $commercialSheetItem->setDesignation($paramJSON['subscriptionName'])
                    ->setPU($paramJSON['price'])
                    ->setQuantity(1)
                    ->setRemise(0)
                    ->setProduct(null)
                    ->setItemOfferType('noStock');

                //Gestion des doublons d'articles dans la BDD
                $commercialSheetItem_ = $manager->getRepository('App:CommercialSheetItem')->findOneBy([
                    'designation' => $commercialSheetItem->getDesignation(),
                    'pu' => $commercialSheetItem->getPU(),
                    'quantity' => $commercialSheetItem->getQuantity(),
                    'remise'   => $commercialSheetItem->getRemise()
                ]);

                if (!empty($commercialSheetItem_)) { //Si il existe
                    //dump('commercialSheetItem exists with id = ' . $commercialSheetItem_->getId());
                    //$commercialSheetItem = $commercialSheetItem_;
                    // $commercialSheetItem_->addCommercialSheet($commercialSheet);
                    // $commercialSheet->addCommercialSheetItem($commercialSheetItem_);
                    // $commercialSheet->removeCommercialSheetItem($commercialSheetItem);
                    $commercialSheetItem = $commercialSheetItem_;
                }

                $commercialSheetItem->addCommercialSheet($commercialSheet);
                $commercialSheet->addCommercialSheetItem($commercialSheetItem);
                $enterprise->addCommercialSheet($commercialSheet);

                //$manager->persist($businessContact);
                $manager->persist($commercialSheetItem);
                $manager->persist($commercialSheet);


                //$manager = $this->getDoctrine()->getManager();
                $manager->persist($enterprise);
                $manager->flush();

                //Envoi d'un mail d'accusé de réception de la commande au client

                //dump($this->getUser());

                return $this->json([
                    'code' => 200,
                    //'message' => 'Empty Array or Not existss !',
                ], 200);
            }
        }

        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 403);
    }

    /**
     * Gestion des commandes marquées livrée et/ou payée
     * 
     * @Route("/admin/invoices/change/status", name="invoices_change_status")
     *
     * @param Request $request
     * @param CommercialSheetRepository $commercialSheetRepo
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    public function changeStatus(Request $request, EntityManagerInterface $manager, MailerInterface $mailer): JsonResponse
    { // , TexterInterface $texter
        $paramJSON = $this->getJSONRequest($request->getContent());
        if (array_key_exists("invoiceDeliveredIds", $paramJSON) && array_key_exists("invoicePaidIds", $paramJSON)) {
            if (!empty($paramJSON['invoiceDeliveredIds']) || !empty($paramJSON['invoicePaidIds'])) {
                if (!empty($paramJSON['invoiceDeliveredIds'])) {
                    foreach ($paramJSON['invoiceDeliveredIds'] as $Id) {
                        $commercialSheet = $manager->getRepository('App:CommercialSheet')->findOneBy(['id' => intval($Id)]);
                        //dump($commercialSheet);
                        if ($commercialSheet) {
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
                if (!empty($paramJSON['invoicePaidIds'])) {
                    foreach ($paramJSON['invoicePaidIds'] as $Id) {
                        $commercialSheet = $manager->getRepository('App:CommercialSheet')->findOneBy(['id' => intval($Id)]);
                        if ($commercialSheet) {
                            $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
                            $commercialSheet->setPaymentStatus(true)
                                ->setPayAt($date)
                                ->setAdvancePayment($commercialSheet->getAmountNetToPaid())
                                ->setDeliveryStatus(true)
                                ->setDeliverAt($date)
                                ->setCompletedStatus(true)
                                ->setCompletedAt($date);

                            $commercialSheet->getEnterprise()->setSubscribeAt($date);
                            $manager->persist($commercialSheet->getEnterprise());
                            // if ($commercialSheet->getDeliveryStatus() && $commercialSheet->getPaymentStatus()) {
                            // }
                            $manager->persist($commercialSheet);

                            foreach ($commercialSheet->getCommercialSheetItems() as $commercialSheetItem) {
                                $subscription = $manager->getRepository('App:Subscription')->findOneBy(['name' => $commercialSheetItem->getDesignation()]);
                                if ($subscription) {
                                    $enterprise = $commercialSheet->getEnterprise();
                                    $enterprise->setSubscription($subscription)
                                        ->setSubscriptionDuration($commercialSheet->getDuration())
                                        ->setIsActivated(true);

                                    $manager->persist($enterprise);

                                    $enterprise = $manager->getRepository('App:Enterprise')->findOneBy([
                                        'socialReason' => 'KnD Technologies'
                                    ]);
                                    $cms = $manager->createQuery("SELECT cms
                                     FROM App\Entity\CommercialSheet cms
                                     JOIN cms.user u
                                     JOIN u.enterprise e
                                     WHERE cms.createdAt LIKE :dat
                                     AND e.id = :entId

                                    ")
                                        ->setParameters([
                                            'dat' => '%' . $commercialSheet->getCreatedAt()->format('Y-m') . '%',
                                            'entId' => $enterprise->getId(),
                                        ])
                                        ->getResult();
                                    $numOrder = 0;
                                    $str = '';
                                    foreach ($cms as $key => $value) {
                                        if ($value === $commercialSheet) $numOrder = $key + 1;
                                    }
                                    //dump($numOrder);

                                    //Envoi d'un mail pour réabonnement au client
                                    $object = 'Facture disponible dans votre compte client';
                                    $message = "Chère cliente, cher client,

Nous vous remercions pour votre commande numéro {$str}{$commercialSheet->getCreatedAt()->format("m")}{$commercialSheet->getCreatedAt()->format("y")}. La facture associée, référence FR{$numOrder}{$commercialSheet->getCreatedAt()->format("m")}{$commercialSheet->getCreatedAt()->format("y")}, d'un montant de {$commercialSheet->getAmountNetToPaid()} XAF, a été éditée :

https://www.kndfactures.com/invoice/{$commercialSheet->getId()}/print

Vous pouvez consulter cette dernière depuis votre compte client en cliquant sur paramètres -> mon compte, puis sur « Mes Factures ».

Nous vous remercions pour la confiance que vous accordez à KnD Factures et restons à votre disposition.

À bientôt sur notre site !
Cordialement,

Votre Service client KnD Factures"; //Pour nous contacter : https://www.ovh.com/fr/support/

                                    foreach ($enterprise->getUsers() as $user) {
                                        if ($user->getRoles()[0] === 'ROLE_ADMIN') {
                                            $this->sendEmail($mailer, $user->getEmail(), $object, $message);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $manager->flush();
                return $this->json([
                    'code' => 200,
                    //'Delivered' => $paramJSON['invoiceDeliveredIds'],
                    'Paid' => $paramJSON['invoicePaidIds'],
                ], 200);
            }
        }
        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 403);
    }

    /**
     * Permet d'afficher la facture d'un document commercial (bill ou quote) pour impression
     * 
     * @Route("/invoice/{id}/print", name="admin_invoice_print")
     * 
     *  @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_ADMIN') and commercialSheet.getEnterprise() == user.getEnterprise() )" )
     *
     * @param CommercialSheet $commercialSheet
     * @return void
     */
    public function printInvoice(CommercialSheet $commercialSheet, EntityManagerInterface $manager)
    { //@IsGranted("ROLE_USER")
        //$inventories = $inventoryRepo->findAll();
        //$date = $commercialSheet->getCreatedAt();
        $enterprise = $manager->getRepository('App:Enterprise')->findOneBy([
            'socialReason' => 'KnD Technologies'
        ]);
        $cms = $manager->createQuery("SELECT cms
                                     FROM App\Entity\CommercialSheet cms
                                     JOIN cms.user u
                                     JOIN u.enterprise e
                                     WHERE cms.createdAt LIKE :dat
                                     AND e.id = :entId

                                    ")
            ->setParameters([
                'dat' => '%' . $commercialSheet->getCreatedAt()->format('Y-m') . '%',
                'entId' => $enterprise->getId(),
            ])
            ->getResult();
        $numOrder = 0;
        $str = '';
        foreach ($cms as $key => $value) {
            if ($value === $commercialSheet) $numOrder = $key + 1;
        }
        //dump($numOrder);

        $str .= $numOrder;

        return $this->render('admin/invoices/print_invoice.html.twig', [
            'commercialSheet' => $commercialSheet,
            'numOrder'        => $str,
        ]);
    }
}
