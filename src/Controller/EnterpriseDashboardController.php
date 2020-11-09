<?php

namespace App\Controller;

use DateTime;
use App\Repository\EnterpriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EnterpriseDashboardController extends ApplicationController
{
    /**
     * @Route("/enterprise/{id<\d+>}/dashbord", name="enterprise_dashbord")
     * 
     * @IsGranted("ROLE_ADMIN")
     * 
     */
    public function index($id, EntityManagerInterface $manager)
    {
        // $startDate = '2020-10-01 00:00:00';
        // $endDate = '2020-10-10 23:59:59';
        // $entRepo = $manager->getRepository('App:EnterpriseRepository');
        // $ent = $entRepo->findOneBy(['id' => $id]);
        if ($this->getUser()->getEnterprise()->getId() == $id) {
            $xturnOverPer = new ArrayCollection();
            $turnOverAmountPer = new ArrayCollection();
            $startDate = new DateTime('2020-10-01 00:00:00 ');
            $endDate = new DateTime('2020-10-22 23:59:59');
            $endDate_ = $endDate->format('Y-m-d');
            $endDate_ = '%' . $endDate_ . '%';

            /*$turnOverPer = $manager->createQuery("SELECT SUBSTRING(cms.createdAt, 1, 13) AS jour, SUM(cms.advancePayment) AS amount
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.paymentStatus = 1)
                                            AND e.id = :entId
                                            AND cms.createdAt LIKE :endDate 
                                            GROUP BY jour
                                            ORDER BY jour ASC
                                                                                    
                                            ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                    //'startDate' => $startDate,
                    'endDate' => $endDate,
                    'type_'   => 'bill',
                ))
                ->getResult();*/
            //dump($turnOverPer);

            /*$bestSellingProducts = $manager->createQuery("SELECT cmsi.designation AS designation, SUM(cmsi.quantity) AS totalSale
                                            FROM App\Entity\CommercialSheetItem cmsi
                                            JOIN cmsi.commercialSheet cms
                                            JOIN cms.user u
                                            JOIN u.enterprise e
                                            WHERE cms.type = 'bill'
                                            AND cms.paymentStatus = 1
                                            AND cmsi.itemOfferType != 'Simple'
                                            AND e.id = :entId
                                            GROUP BY designation
                                            ORDER BY totalSale DESC 
                                                                                                                            
                                        ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                ))
                ->setMaxResults(10)
                ->getResult();*/
            //dump($bestSellingProducts);

            /*$nbProductsSold = $manager->createQuery("SELECT COUNT(DISTINCT cmsi.designation) AS Designation
                                            FROM App\Entity\CommercialSheetItem cmsi
                                            JOIN cmsi.commercialSheet cms
                                            JOIN cms.user u
                                            JOIN u.enterprise e
                                            WHERE cms.type = 'bill'
                                            AND cmsi.itemOfferType != 'Simple'
                                            AND cms.paymentStatus = 1
                                            AND e.id = :entId
                                                                                                                                                                        
                                        ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                ))
                ->getResult();*/


            /*$salePerCategory = $manager->createQuery("SELECT cat.name AS name_, SUM(cmsi.quantity) AS qty
                                            FROM App\Entity\Category cat, App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN cms.commercialSheetItems cmsi
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND cmsi.itemOfferType != 'Simple'
                                            AND e.id = :entId
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate  
                                            GROUP BY name_                                                                                
                                        ")
                ->setParameters(array(
                    'entId'     => $this->getUser()->getEnterprise()->getId(),
                    'startDate' => $startDate->format('Y-m-d H:m:i'),
                    'endDate'   => $endDate->format('Y-m-d H:m:i'),
                    'type_'     => 'bill',
                ))
                ->getResult();
            $salePerCategory = $manager->createQuery("SELECT cat.name AS name_, SUM(cmsi.quantity) AS qty
                                            FROM App\Entity\Category cat,
                                            JOIN cms.user u 
                                            JOIN cat.enterprise e
                                            RIGHT JOIN cat.products p
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND cmsi.itemOfferType != 'Simple'
                                            AND e.id = :entId
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate  
                                            GROUP BY name_                                                                                
                                        ")
                ->setParameters(array(
                    'entId'     => $this->getUser()->getEnterprise()->getId(),
                    'startDate' => $startDate->format('Y-m-d H:m:i'),
                    'endDate'   => $endDate->format('Y-m-d H:m:i'),
                    'type_'     => 'bill',
                ))
                ->getResult();*/

            /*$turnOverPer = $manager->createQuery("SELECT SUBSTRING(cms.deliverAt, 1, 10) AS jour,
                                        SUM(cmsi.pu * cmsi.quantity) AS amount
                                        FROM App\Entity\CommercialSheet cms
                                        JOIN cms.commercialSheetItems cmsi
                                        JOIN cms.user u 
                                        JOIN u.enterprise e
                                        WHERE cms.type = :type_
                                        AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                        AND e.id = :entId
                                        AND cms.deliverAt >= :startDate                                                                                  
                                        AND cms.deliverAt <= :endDate
                                        GROUP BY jour
                                        ORDER BY jour ASC
                                                                                
                                        ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                    'startDate' => $startDate->format('Y-m-d H:m:i'),
                    'endDate'   => $endDate->format('Y-m-d H:m:i'),
                    'type_'   => 'bill',
                ))
                ->getResult();*/
            //dump($turnOverPer);
            //dd($salePerCategory);
            //dump($nbProductsSold);
            // dump(gettype(floatval($billCompleted[0]['TotalPayment'])));
            // dd(floatval($billPartial[0]['advanceTotal']));

            return $this->render('enterprise_dashboard/index.html.twig', []);
        }
    }

    /**
     * Permet la MAJ du dashboard client entreprise
     * 
     * @Route("/enterprise/dashboard/update/", name="enterprise_dash_update")
     * 
     * @IsGranted("ROLE_ADMIN") 
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function updateEnterpriseDashboard(Request $request, EntityManagerInterface $manager)
    {
        $paramJSON = $this->getJSONRequest($request->getContent());
        if ((array_key_exists("startDate", $paramJSON) && !empty($paramJSON['startDate'])) && (array_key_exists("endDate", $paramJSON) && !empty($paramJSON['endDate'])) && (array_key_exists("ent", $paramJSON) && !empty($paramJSON['ent']))) {
            //$interval = $endDate->diff($startDate);
            /*if ($interval) {
                // dump($interval->invert);
                //dump($interval->format('%R%a days'));
                // dd(gettype($interval->days));
                //dd(gettype($interval->format('d')));
                //return $interval->format('%R%a days');// '+29 days'
                //return $interval->days; //Nombre de jour total de différence entre les dates 
                //return !$interval->invert; // 

                return $interval->days;
            }*/
            $types   = ['bill', 'quote', 'purchaseorder'];
            $sheetNb = [];
            $convertedQuoteNb = null;
            $cmss = null;
            $nbNewCustomer = null;
            $turnOverHT = 0.0;
            $amountRecettes = 0.0;
            $expensesTTC = 0.0;
            $outstandingClaim = 0.0;
            $outstandingDebt = 0.0;
            // $nbProductsSold_ = null;
            // $turnOverPer = null;
            // $expensesPer = null;
            $billNb = 0;
            $quoteNb = 0;
            $purchaseNb = 0;
            $converted = 0;
            $nbProductsSold = 0;
            $per        = '';
            $xturnOverPer = new ArrayCollection();
            $turnOverAmountPer = new ArrayCollection();
            // $xamountRecettesPer = new ArrayCollection();
            // $amountRecettesPer = new ArrayCollection();
            // $xexpensesPer = new ArrayCollection();
            // $expensesAmountPer = new ArrayCollection();

            $startDate = new DateTime($paramJSON['startDate']);
            $endDate = new DateTime($paramJSON['endDate']);

            $interval = $startDate->diff($endDate);

            if ($interval->days == 0) {
                $endDate_ = $endDate->format('Y-m-d');
                $endDate_ = '%' . $endDate_ . '%';
                $per = 'hour';

                //Détermination du nombre de Document réalisé par type
                foreach ($types as $type) {
                    //dump($type);
                    $sheetNb['' . $type] = $manager->createQuery("SELECT COUNT(cms) AS sheetNb 
                                                FROM App\Entity\CommercialSheet cms
                                                LEFT JOIN cms.user u 
                                                JOIN u.enterprise e
                                                WHERE cms.type = :type_
                                                AND e.id = :entId
                                                AND cms.createdAt LIKE :dat                                                                                                                                  
                                            ")
                        ->setParameters(array(
                            'entId'   => $this->getUser()->getEnterprise()->getId(),
                            'type_'   => $type,
                            'dat'     => $endDate_,
                        ))
                        ->getResult();
                }
                //Détermination du nombre de Dévis convertis en Facture
                $convertedQuoteNb = $manager->createQuery("SELECT COUNT(cms) AS convertQuoteNb 
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.convertFlag = 1
                                            AND e.id = :entId
                                            AND cms.createdAt LIKE :dat                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                    ))
                    ->getResult();

                //Détermination du chiffre d'affaire HT SUBSTRING(cms.deliverAt, 1, 13)
                /*$bills = $manager->createQuery("SELECT cms.deliverAt AS jour,
                                             SUM( (cmsi.pu * cmsi.quantity) - ( ( (cmsi.pu * cmsi.quantity) * cmsi.remise ) / 100.0 ) ) - cms.fixReduction AS CAHT
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.commercialSheetItems cmsi
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt LIKE :dat  
                                            GROUP BY jour 
                                            ORDER BY jour ASC                                                                               
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'bill',
                    ))
                    ->getResult();

                $index = 0;
                $turnOverHT = 0;
                foreach ($bills as $d) {
                    $date        = $d['jour']->format('d-m-Y H'); //new DateTime();
                    $tmp         = $d['CAHT'] == null ? '0' : number_format((float) floatval($d['CAHT']), 2, '.', '');
                    $turnOverHT += $tmp;
                    if (!$xturnOverPer->contains($date)) {
                        $xturnOverPer[]       = $date;
                        $turnOverAmountPer[$index]  = $tmp;
                    } else {
                        //$tmp                         = $d['CAHT'] == null ? '0' : number_format((float) floatval($d['CAHT']), 2, '.', '');
                        $turnOverAmountPer[$index]  += $tmp;
                    }
                    $index++;
                }
                $turnOverHT = number_format((float) $turnOverHT, 2, '.', ' ');*/

                $bills = $manager->createQuery("SELECT cms
                                                FROM App\Entity\CommercialSheet cms
                                                JOIN cms.user u 
                                                JOIN u.enterprise e
                                                WHERE cms.type = :type_
                                                AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                                AND e.id = :entId
                                                AND cms.deliverAt LIKE :dat  
                                                ORDER BY cms.deliverAt ASC                                                                               
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'bill',
                    ))
                    ->getResult();

                //dd($bills);
                $index = 0;
                $index2 = 0;
                $precIndex = 0;
                $precIndex2 = 0;
                $turnOverHT = 0;
                foreach ($bills as $commercialSheet) {
                    $date        = $commercialSheet->getDeliverAt()->format('d-m-Y H'); //new DateTime();
                    $tmp         = $commercialSheet->getTotalAmountNetHT();
                    $tmp         = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                    $turnOverHT += $tmp;
                    if (!$xturnOverPer->contains($date)) {
                        $xturnOverPer[]             = $date;
                        $turnOverAmountPer[$index]  = $tmp;
                        $precIndex                  = $index;
                        $index++;
                    } else {
                        //$tmp                         = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                        $turnOverAmountPer[$precIndex]  += $tmp;
                    }

                    //$date            = $commercialSheet->getDeliverAt()->format('d-m-Y H'); //new DateTime();
                    $tmp             = $commercialSheet->getAdvancePayment();
                    $tmp             = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                    $amountRecettes += $tmp;
                    foreach ($commercialSheet->getCommercialSheetItems() as $commercialSheetItem) {
                        if ($commercialSheetItem->getItemOfferType() != 'Simple') {
                            $nbProductsSold += $commercialSheetItem->getQuantity();
                        }
                    }
                }
                $turnOverHT = number_format((float) $turnOverHT, 2, '.', ' ');
                $amountRecettes = number_format((float) $amountRecettes, 2, '.', '');
                // dump($turnOverHT);
                // dump($xturnOverPer);
                // dd($turnOverAmountPer);
                // dump($turnOverHT);
                // dump($xturnOverPer);
                // dump($turnOverAmountPer);
                //Détermination du montant des Réduction
                /*$RecettesPer = $manager->createQuery("SELECT cms.deliverAt AS jour, cms.advancePayment AS amountRecettes
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt LIKE :dat 
                                            GROUP BY jour 
                                            ORDER BY jour ASC                                                                               
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'bill',
                    ))
                    ->getResult();
                
                $index = 0;
                $amountRecettes = 0;
                foreach ($RecettesPer as $d) {
                    $date        = $d['jour']->format('d-m-Y H'); //new DateTime();
                    $tmp         = $d['amountRecettes'] == null ? '0' : number_format((float) floatval($d['amountRecettes']), 2, '.', '');
                    $amountRecettes += $tmp;
                    if (!$xamountRecettesPer->contains($date)) {
                        $xamountRecettesPer[]       = $date;
                        $amountRecettesPer[$index]  = $tmp;
                    } else {
                        //$tmp                         = $d['amountRecettes'] == null ? '0' : number_format((float) floatval($d['amountRecettes']), 2, '.', '');
                        $amountRecettesPer[$index]  += $tmp;
                    }
                    $index++;
                }
                $amountRecettes = number_format((float) $amountRecettes, 2, '.', ' ');
                // dump($amountRecettes);
                // dump($xamountRecettesPer);
                // dump($amountRecettesPer);
                */

                /*$RecettesPer = $manager->createQuery("SELECT cms
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt LIKE :dat 
                                            ORDER BY cms.deliverAt ASC                                                                               
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'bill',
                    ))
                    ->getResult();

                $index = 0;
                $precIndex = 0;
                $amountRecettes = 0;
                foreach ($RecettesPer as $commercialSheet) {
                    $date            = $commercialSheet->getDeliverAt()->format('d-m-Y H'); //new DateTime();
                    $tmp             = $commercialSheet->getAdvancePayment();
                    $tmp             = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                    $amountRecettes += $tmp;
                    if (!$xamountRecettesPer->contains($date)) {
                        $xamountRecettesPer[]       = $date;
                        $amountRecettesPer[$index]  = $tmp;
                        $precIndex = $index;
                        $index++;
                    } else {
                        //$tmp                         = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                        $amountRecettesPer[$precIndex]  += $tmp;
                    }
                }
                $amountRecettes = number_format((float) $amountRecettes, 2, '.', ' ');*/

                // dump($amountRecettes);
                // dump($xamountRecettesPer);
                // dump($amountRecettesPer);
                //Détermination des dépenses fournisseurs TTC
                /*$purchaseOrders = $manager->createQuery("SELECT cms.deliverAt AS jour, cms.advancePayment AS EXTTC
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            JOIN cms.commercialSheetItems cmsi
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt LIKE :dat                                                                                  
                                            GROUP BY jour
                                            ORDER BY jour ASC
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();

                $index = 0;
                $expensesTTC = 0;
                foreach ($purchaseOrders as $d) {
                    $date        = $d['jour']->format('d-m-Y H'); //new DateTime();
                    $tmp         = $d['EXTTC'] == null ? '0' : number_format((float) floatval($d['EXTTC']), 2, '.', '');
                    $expensesTTC += $tmp;
                    if (!$xexpensesPer->contains($date)) {
                        $xexpensesPer[]       = $date;
                        $expensesAmountPer[$index]  = $tmp;
                    } else {
                        //$tmp                         = $d['EXTTC'] == null ? '0' : number_format((float) floatval($d['EXTTC']), 2, '.', '');
                        $expensesAmountPer[$index]  += $tmp;
                    }
                    $index++;
                }
                $expensesTTC = number_format((float) $expensesTTC, 2, '.', ' ');
                // dump($expensesTTC);
                // dump($xexpensesPer);
                // dump($expensesAmountPer);
                */

                $purchaseOrders = $manager->createQuery("SELECT cms
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt LIKE :dat                                                                                  
                                            ORDER BY cms.deliverAt ASC
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();

                $index = 0;
                $precIndex = 0;
                $expensesTTC = 0;
                foreach ($purchaseOrders as $commercialSheet) {
                    $date            = $commercialSheet->getDeliverAt()->format('d-m-Y H'); //new DateTime();
                    $tmp             = $commercialSheet->getAdvancePayment();
                    $tmp             = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                    $expensesTTC += $tmp;
                    /*if (!$xexpensesPer->contains($date)) {
                        $xexpensesPer[]       = $date;
                        $expensesAmountPer[$index]  = $tmp;
                        $precIndex = $index;
                        $index++;
                    } else {
                        //$tmp                         = $d['EXTTC'] == null ? '0' : number_format((float) floatval($d['EXTTC']), 2, '.', '');
                        $expensesAmountPer[$precIndex]  += $tmp;
                    }*/
                }
                $expensesTTC = number_format((float) $expensesTTC, 2, '.', '');

                $cmss = $manager->createQuery("SELECT cms 
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = 'bill'
                                            AND e.id = :entId
                                            AND cms.deliveryStatus = 1 OR cms.completedStatus = 1
                                            AND e.id = :entId
                                            AND cms.deliverAt LIKE :dat                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                    ))
                    ->getResult();
                //dump($cmss);

                /*foreach ($purchaseOrders as $purchaseOrder_) {
                    $tmp          = $purchaseOrder_['EXTTC'] == null ? 0 : number_format((float) floatval($purchaseOrder_['EXTTC']), 2, '.', '');
                    $expensesTTC += $tmp;
                }
                $expensesTTC = number_format((float) $expensesTTC, 2, '.', ' ');
                foreach ($purchaseOrders as $d) {
                    $xexpensesPer[]       = $d['jour'];
                    $tmp                  = $d['EXTTC'] == null ? '0' : number_format((float) floatval($d['EXTTC']), 2, '.', '');
                    $expensesAmountPer[]  = $tmp;
                }*/

                //Chiffre d'affaire HT par heure de la journée
                /*$turnOverPer = $manager->createQuery("SELECT SUBSTRING(cms.deliverAt, 1, 13) AS jour,
                                        SUM(cmsi.pu * cmsi.quantity) AS amount
                                        FROM App\Entity\CommercialSheet cms
                                        JOIN cms.commercialSheetItems cmsi
                                        JOIN cms.user u 
                                        JOIN u.enterprise e
                                        WHERE cms.type = :type_
                                        AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                        AND e.id = :entId
                                        AND cms.deliverAt LIKE :endDate 
                                        GROUP BY jour
                                        ORDER BY jour ASC
                                                                                
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        //'startDate' => $startDate,
                        'endDate' => $endDate_,
                        'type_'   => 'bill',
                    ))
                    ->getResult();

                //Dépenses TTC par heure de la journée
                $expensesPer = $manager->createQuery("SELECT SUBSTRING(cms.deliverAt, 1, 13) AS jour,
                                        SUM(cmsi.pu * cmsi.quantity) + ((SUM(cmsi.pu * cmsi.quantity) * e.tva) / 100.0) - ( ( SUM(cmsi.pu * cmsi.quantity) * cms.itemsReduction ) / 100.0) - cms.fixReduction AS amount
                                        FROM App\Entity\CommercialSheet cms
                                        JOIN cms.user u 
                                        JOIN cms.commercialSheetItems cmsi
                                        JOIN u.enterprise e
                                        WHERE cms.type = :type_
                                        AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                        AND e.id = :entId
                                        AND cms.deliverAt LIKE :endDate 
                                        GROUP BY jour
                                        ORDER BY jour ASC
                                                                                
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        //'startDate' => $startDate,
                        'endDate' => $endDate_,
                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();*/
            } else {
                $per = 'day';
                $startDate = new DateTime($paramJSON['startDate'] . ' 00:00:00');
                $endDate = new DateTime($paramJSON['endDate'] . ' 23:59:59');

                //Détermination du chiffre d'affaire HT
                /*$bills = $manager->createQuery("SELECT SUBSTRING(cms.deliverAt, 1, 10) AS jour,
                                             SUM( (cmsi.pu * cmsi.quantity) - ( ( (cmsi.pu * cmsi.quantity) * cmsi.remise ) / 100.0 ) ) - cms.fixReduction AS CAHT
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            JOIN cms.commercialSheetItems cmsi
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate  
                                            GROUP BY jour 
                                            ORDER BY jour ASC                                                                               
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate'   => $endDate->format('Y-m-d H:m:i'),
                        'type_'     => 'bill',
                    ))
                    ->getResult();


                foreach ($bills as $bill_) {
                    $tmp         = $bill_['CAHT'] == null ? 0 : number_format((float) floatval($bill_['CAHT']), 2, '.', '');
                    $turnOverHT += $tmp;
                }
                $turnOverHT = number_format((float) $turnOverHT, 2, '.', ' ');
                foreach ($bills as $d) {
                    $xturnOverPer[]        = $d['jour'];
                    $tmp                   = $d['CAHT'] == null ? '0' : number_format((float) floatval($d['CAHT']), 2, '.', '');
                    $turnOverAmountPer[]   = $tmp;
                }
                */

                $bills = $manager->createQuery("SELECT cms
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate  
                                            ORDER BY cms.deliverAt ASC                                                                               
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate'   => $endDate->format('Y-m-d H:m:i'),
                        'type_'     => 'bill',
                    ))
                    ->getResult();


                $index = 0;
                $precIndex = 0;
                $index2 = 0;
                $precIndex2 = 0;
                $turnOverHT = 0;
                foreach ($bills as $commercialSheet) {
                    $date        = $commercialSheet->getDeliverAt()->format('d-m-Y'); //new DateTime();
                    $tmp         = $commercialSheet->getTotalAmountNetHT();
                    $tmp         = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                    $turnOverHT += $tmp;
                    if (!$xturnOverPer->contains($date)) {
                        $xturnOverPer[]             = $date;
                        $turnOverAmountPer[$index]  = $tmp;
                        $precIndex                  = $index;
                        $index++;
                    } else {
                        //$tmp                         = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                        $turnOverAmountPer[$precIndex]  += $tmp;
                    }

                    //$date            = $commercialSheet->getDeliverAt()->format('d-m-Y'); //new DateTime();
                    $tmp             = $commercialSheet->getAdvancePayment();
                    $tmp             = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                    $amountRecettes += $tmp;
                    foreach ($commercialSheet->getCommercialSheetItems() as $commercialSheetItem) {
                        if ($commercialSheetItem->getItemOfferType() != 'Simple') {
                            $nbProductsSold += $commercialSheetItem->getQuantity();
                        }
                    }
                }
                $turnOverHT = number_format((float) $turnOverHT, 2, '.', ' ');
                $amountRecettes = number_format((float) $amountRecettes, 2, '.', '');

                //Détermination du montant des Réduction
                /*$amountReduction = $manager->createQuery("SELECT cms.deliverAt AS jour, ((SUM(cmsi.pu * cmsi.quantity) * cms.itemsReduction ) / 100.0) + cms.fixReduction AS amountReduction
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            JOIN cms.commercialSheetItems cmsi
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate   
                                            GROUP BY jour                                                                                 
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate'   => $endDate->format('Y-m-d H:m:i'),
                        'type_'     => 'bill',
                    ))
                    ->getResult();*/
                /*$RecettesPer = $manager->createQuery("SELECT SUBSTRING(cms.deliverAt, 1, 10) AS jour, cms.advancePayment AS amountRecettes
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            JOIN cms.commercialSheetItems cmsi
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate   
                                            GROUP BY jour 
                                            ORDER BY jour ASC                                                                                
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate'   => $endDate->format('Y-m-d H:m:i'),
                        'type_'     => 'bill',
                    ))
                    ->getResult();

                foreach ($RecettesPer as $amountRecettes_) {
                    $tmp       = $amountRecettes_['amountRecettes'] == null ? 0 : number_format((float) floatval($amountRecettes_['amountRecettes']), 2, '.', '');
                    $amountRecettes += $tmp;
                }
                $amountRecettes = number_format((float) $amountRecettes, 2, '.', ' ');

                foreach ($RecettesPer as $d) {
                    $xamountRecettesPer[] = $d['jour'];
                    $tmp                  = $d['amountRecettes'] == null ? '0' : number_format((float) floatval($d['amountRecettes']), 2, '.', '');
                    $amountRecettesPer[]  = $tmp;
                }
                */

                /*$RecettesPer = $manager->createQuery("SELECT cms
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate   
                                            ORDER BY cms.deliverAt ASC                                                                                
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate'   => $endDate->format('Y-m-d H:m:i'),
                        'type_'     => 'bill',
                    ))
                    ->getResult();


                $index = 0;
                $precIndex = 0;
                $amountRecettes = 0;
                foreach ($RecettesPer as $commercialSheet) {
                    $date            = $commercialSheet->getDeliverAt()->format('d-m-Y'); //new DateTime();
                    $tmp             = $commercialSheet->getAdvancePayment();
                    $tmp             = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                    $amountRecettes += $tmp;
                    if (!$xamountRecettesPer->contains($date)) {
                        $xamountRecettesPer[]       = $date;
                        $amountRecettesPer[$index]  = $tmp;
                        $precIndex = $index;
                        $index++;
                    } else {
                        //$tmp                         = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                        $amountRecettesPer[$precIndex]  += $tmp;
                    }
                }
                $amountRecettes = number_format((float) $amountRecettes, 2, '.', ' ');
                */

                //Détermination des dépenses fournisseurs TTC
                /*$purchaseOrders = $manager->createQuery("SELECT SUBSTRING(cms.deliverAt, 1, 10) AS jour, cms.advancePayment AS EXTTC
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            JOIN cms.commercialSheetItems cmsi
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate                                                                                   
                                            GROUP BY jour
                                            ORDER BY jour ASC
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate'   => $endDate->format('Y-m-d H:m:i'),
                        'type_'     => 'purchaseorder',
                    ))
                    ->getResult();


                foreach ($purchaseOrders as $purchaseOrder_) {
                    $tmp          = $purchaseOrder_['EXTTC'] == null ? 0 : number_format((float) floatval($purchaseOrder_['EXTTC']), 2, '.', '');
                    $expensesTTC += $tmp;
                }
                $expensesTTC = number_format((float) $expensesTTC, 2, '.', ' ');
                foreach ($purchaseOrders as $d) {
                    $xexpensesPer[]       = $d['jour'];
                    $tmp                  = $d['EXTTC'] == null ? '0' : number_format((float) floatval($d['EXTTC']), 2, '.', '');
                    $expensesAmountPer[]  = $tmp;
                }
                */

                $purchaseOrders = $manager->createQuery("SELECT cms
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate                                                                                   
                                            ORDER BY cms.deliverAt ASC
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate'   => $endDate->format('Y-m-d H:m:i'),
                        'type_'     => 'purchaseorder',
                    ))
                    ->getResult();


                $index = 0;
                $precIndex = 0;
                $expensesTTC = 0;
                foreach ($purchaseOrders as $commercialSheet) {
                    $date            = $commercialSheet->getDeliverAt()->format('d-m-Y'); //new DateTime();
                    $tmp             = $commercialSheet->getAdvancePayment();
                    $tmp             = $tmp == null ? '0' : number_format((float) floatval($tmp), 2, '.', '');
                    $expensesTTC += $tmp;
                    /*if (!$xexpensesPer->contains($date)) {
                        $xexpensesPer[]       = $date;
                        $expensesAmountPer[$index]  = $tmp;
                        $precIndex = $index;
                        $index++;
                    } else {
                        //$tmp                         = $d['EXTTC'] == null ? '0' : number_format((float) floatval($d['EXTTC']), 2, '.', '');
                        $expensesAmountPer[$precIndex]  += $tmp;
                    }*/
                }
                $expensesTTC = number_format((float) $expensesTTC, 2, '.', '');

                //Chiffre d'affaire HT par jour de l'intervalle de date
                /*$turnOverPer = $manager->createQuery("SELECT SUBSTRING(cms.deliverAt, 1, 10) AS jour,
                                        SUM(cmsi.pu * cmsi.quantity) AS amount
                                        FROM App\Entity\CommercialSheet cms
                                        JOIN cms.user u 
                                        JOIN cms.commercialSheetItems cmsi
                                        JOIN u.enterprise e
                                        WHERE cms.type = :type_
                                        AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                        AND e.id = :entId
                                        AND cms.deliverAt >= :startDate                                                                                  
                                        AND cms.deliverAt <= :endDate                                                                                   
                                        GROUP BY jour
                                        ORDER BY jour ASC
                                                                                
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                        'type_'     => 'bill',
                    ))
                    ->getResult();

                //Dépenses TTC par jour de l'intervalle de date
                $expensesPer = $manager->createQuery("SELECT SUBSTRING(cms.deliverAt, 1, 10) AS jour,
                                        SUM(cmsi.pu * cmsi.quantity) + ((SUM(cmsi.pu * cmsi.quantity) * e.tva) / 100.0) - ( ( SUM(cmsi.pu * cmsi.quantity) * cms.itemsReduction ) / 100.0) - cms.fixReduction AS amount
                                        FROM App\Entity\CommercialSheet cms
                                        JOIN cms.user u 
                                        JOIN cms.commercialSheetItems cmsi
                                        JOIN u.enterprise e
                                        WHERE cms.type = :type_
                                        AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                        AND e.id = :entId
                                        AND cms.deliverAt >= :startDate                                                                                  
                                        AND cms.deliverAt <= :endDate                                                                                   
                                        GROUP BY jour
                                        ORDER BY jour ASC
                                                                                
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();*/


                foreach ($types as $type) {
                    //dump($type);
                    $sheetNb['' . $type] = $manager->createQuery("SELECT COUNT(cms) AS sheetNb 
                                                FROM App\Entity\CommercialSheet cms
                                                LEFT JOIN cms.user u 
                                                JOIN u.enterprise e
                                                WHERE cms.type = :type_
                                                AND e.id = :entId
                                                AND cms.createdAt >= :startDate                                                                                  
                                                AND cms.createdAt <= :endDate                                                                                                                                  
                                            ")
                        ->setParameters(array(
                            'entId'   => $this->getUser()->getEnterprise()->getId(),
                            'type_'   => $type,
                            'startDate' => $startDate->format('Y-m-d H:m:i'),
                            'endDate' => $endDate->format('Y-m-d H:m:i'),

                        ))
                        ->getResult();
                }
                $convertedQuoteNb = $manager->createQuery("SELECT COUNT(cms) AS convertQuoteNb 
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.convertFlag = 1
                                            AND e.id = :entId
                                            AND cms.createdAt >= :startDate                                                                                  
                                            AND cms.createdAt <= :endDate                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate,
                        'endDate' => $endDate,

                    ))
                    ->getResult();

                /*$nbProductsSold_ = $manager->createQuery("SELECT SUM(cmsi.quantity) AS Qty
                                            FROM App\Entity\CommercialSheetItem cmsi
                                            INNER JOIN cmsi.commercialSheet cms
                                            INNER JOIN cms.user u
                                            JOIN u.enterprise e
                                            WHERE cms.type = 'bill'
                                            AND cmsi.itemOfferType != 'Simple'
                                            AND cms.deliveryStatus = 1 OR cms.completedStatus = 1
                                            AND e.id = :entId
                                            AND cms.createdAt >= :startDate                                                                                  
                                            AND cms.createdAt <= :endDate                                                                                                                            
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                    ))
                    ->getResult();*/



                $cmss = $manager->createQuery("SELECT cms 
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = 'bill'
                                            AND (cms.deliveryStatus = 1 OR cms.completedStatus = 1)
                                            AND cms.deliverAt >= :startDate                                                                                  
                                            AND cms.deliverAt <= :endDate                                                                                  
                                            AND e.id = :entId
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                    ))
                    ->getResult();
                //dump($cmss);
                $nbNewCustomer = $manager->createQuery("SELECT COUNT(b.socialReason) AS nbNewCustomer
                                            FROM App\Entity\BusinessContact b, App\Entity\Enterprise e
                                            WHERE b.type = 'customer'
                                            AND b.createdAt >= :startDate                                                                                  
                                            AND b.createdAt <= :endDate                                                                                  
                                            AND e.id = :entId
                                        ")
                    ->setParameters(array(
                        'entId'     => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                    ))
                    ->getResult();
                dump($nbNewCustomer[0]['nbNewCustomer']);
            }
            //dump($nbProductsSold);
            $billPaymentOnpending = $manager->createQuery("SELECT cms AS paymentOnPending
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND e.id = :entId
                                                                                                                             
                                        ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                    'type_'   => 'bill',
                ))
                ->getResult();

            $purchaseOrderPaymentOnpending = $manager->createQuery("SELECT cms AS paymentOnPending
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND e.id = :entId
                                                                                                                              
                                        ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                    'type_'   => 'purchaseorder',
                ))
                ->getResult();

            $outstandingClaim = 0.0;
            foreach ($billPaymentOnpending as $commercialSheet) {
                //dump($commercialSheet['paymentOnPending']->getAmountRestToPaid());
                $outstandingClaim += $commercialSheet['paymentOnPending']->getAmountRestToPaid();
            }
            $outstandingClaim = number_format((float) $outstandingClaim, 2, '.', ' ');
            $outstandingDebt = 0.0;
            foreach ($purchaseOrderPaymentOnpending as $commercialSheet) {
                $outstandingDebt += $commercialSheet['paymentOnPending']->getAmountRestToPaid();
            }
            $outstandingDebt = number_format((float) $outstandingDebt, 2, '.', ' ');

            $bestSellingProducts = [];
            /*$bestSellingProducts = $manager->createQuery("SELECT cmsi.designation AS designation,
                                            cmsi.reference AS ref,cmsi.pu AS pu, SUM(cmsi.pu*cmsi.quantity) AS amount,
                                            SUM(cmsi.quantity) AS totalSale
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.commercialSheetItems cmsi
                                            JOIN cms.user u
                                            JOIN u.enterprise e
                                            WHERE cms.type = 'bill'
                                            AND e.id = :entId
                                            AND cms.deliveryStatus = 1 OR cms.completedStatus = 1
                                            AND cmsi.itemOfferType != 'Simple'
                                            GROUP BY designation, ref, pu
                                            ORDER BY totalSale DESC                                                       
                                        ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                ))
                //->setMaxResults(10)
                ->getResult();*/
            /*$bestSellingProducts = $manager->createQuery("SELECT cms
                                            FROM App\Entity\CommercialSheet cms
                                            JOIN cms.user u
                                            JOIN u.enterprise e
                                            WHERE cms.type = 'bill'
                                            AND e.id = :entId
                                            AND cms.deliveryStatus = 1 OR cms.completedStatus = 1
                                                                                                   
                                        ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                ))
                //->setMaxResults(10)
                ->getResult();
            dump($bestSellingProducts);*/
            //$cmss = $manager->getRepository('App:CommercialSheet')->findAll();
            $index = 0;
            foreach ($cmss as $cms) {
                if (($cms->getType() === 'bill') && ($cms->getUser()->getEnterprise() === $this->getUser()->getEnterprise())) {
                    if ($cms->getDeliveryStatus() == true || $cms->getCompletedStatus() == true) {
                        foreach ($cms->getCommercialSheetItems() as $cmsi) {
                            if ($cmsi->getItemOfferType() !== 'Simple') {
                                $isNew = false;
                                foreach ($bestSellingProducts as $key => $value) {
                                    if ((array_key_exists("designation", $value) && !empty($value['designation'])) && (array_key_exists("ref", $value) && !empty($value['ref'])) && (array_key_exists("pu", $value) && !empty($value['pu']))) {
                                        if (($value['designation'] === $cmsi->getDesignation()) && ($value['ref'] === $cmsi->getReference()) && ($value['pu'] === $cmsi->getPu())) {
                                            //dump($value);
                                            //dump($value['designation']);
                                            $bestSellingProducts[$key]['totalSale'] += $cmsi->getQuantity();
                                            $tmp = $cmsi->getQuantity() * $cmsi->getPu();
                                            $remise = ($tmp * $cmsi->getRemise()) / 100.0;
                                            $tmp = $tmp - $remise;
                                            $bestSellingProducts[$key]['amount'] += $tmp;
                                            $isNew = true;
                                        }
                                    }
                                }
                                if (!$isNew) {
                                    $tmp = $cmsi->getQuantity() * $cmsi->getPu();
                                    $remise = ($tmp * $cmsi->getRemise()) / 100.0;
                                    $tmp = $tmp - $remise;

                                    $bestSellingProducts[$index++] = [
                                        'designation' => $cmsi->getDesignation(),
                                        'ref'         => $cmsi->getReference(),
                                        'pu'          => $cmsi->getPu(),
                                        'totalSale'   => $cmsi->getQuantity(),
                                        'amount'      => $tmp
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($bestSellingProducts)) {
                //Rangement par ordre décroissant de total de vente
                usort($bestSellingProducts, function ($item1, $item2) {
                    return $item2['totalSale'] <=> $item1['totalSale'];
                });
            }
            $bestSellingProdCategory = [];
            $tmpArray = new ArrayCollection();
            // $categoryRepo = $manager->getRepository('App:Category');
            // $categories   = $categoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
            $productRepo  = $manager->getRepository('App:Product');
            $index = 0;
            foreach ($bestSellingProducts as $prodArray) {
                if ((array_key_exists("designation", $prodArray) && !empty($prodArray['designation'])) && (array_key_exists("ref", $prodArray) && !empty($prodArray['ref'])) && (array_key_exists("pu", $prodArray) && !empty($prodArray['pu']))) {
                    $product = $productRepo->findOneBy([
                        'name'  => $prodArray['designation'],
                        'sku'   => $prodArray['ref'],
                        'price' => $prodArray['pu']
                    ]);
                    if ($product) {
                        $categories = $product->getCategories();
                        if (!empty($categories)) {
                            foreach ($categories as $category) {
                                $amount    = floatval($prodArray['amount']);
                                $totalSale = intval($prodArray['totalSale']);
                                //if (array_key_exists('' . $category->getName(), $tmpArray)) {
                                if ($tmpArray->contains($category)) {
                                    foreach ($tmpArray as $key => $value) {
                                        if ($value === $category) {
                                            $bestSellingProdCategory[$key]['totalSale'] += $totalSale;
                                            $bestSellingProdCategory[$key]['amount'] += $amount;
                                        }
                                    }
                                } else {
                                    //$tmpArray[] = '' . $category->getName();
                                    $tmpArray[] = $category;
                                    //$index++;
                                    $bestSellingProdCategory[$index++] = [
                                        'name'      => $category->getName(),
                                        'totalSale' => $totalSale,
                                        'amount'    => $amount,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($bestSellingProdCategory)) {
                //Rangement par ordre décroissant de total de vente
                usort($bestSellingProdCategory, function ($item1, $item2) {
                    return $item2['totalSale'] <=> $item1['totalSale'];
                });
            }

            $nbCustomer = 0;

            $customers = $this->getUser()->getEnterprise()->getBusinessContacts();
            foreach ($customers as $customer) {
                if ($customer->getType() === 'customer') $nbCustomer++;
            }
            dump($nbCustomer);
            //dump($bestSellingProdCategory);
            /*foreach ($turnOverPer as $d) {
                $xturnOverPer[] = $d['jour'];
                $turnOverAmountPer[]   = number_format((float) $d['amount'], 2, '.', '');
            }

            foreach ($expensesPer as $d) {
                $xexpensesPer[] = $d['jour'];
                $expensesAmountPer[]   = number_format((float) $d['amount'], 2, '.', '');
            }*/

            // $nbProductsSold = $nbProductsSold_[0]['Qty'];
            $billNb     = $sheetNb['bill'][0]['sheetNb'];
            $quoteNb    = $sheetNb['quote'][0]['sheetNb'];
            $purchaseNb = $sheetNb['purchaseorder'][0]['sheetNb'];
            $converted  = $convertedQuoteNb[0]['convertQuoteNb'];

            return $this->json([
                'code'                    => 200,
                'turnOverHT'              => $turnOverHT,
                //'expenses'                => $expensesTTC,
                //'amountRecettes'          => $amountRecettes,
                'flux_tresorerie'         => [$amountRecettes, $expensesTTC],
                'turnOverAmountPer'       => $turnOverAmountPer,
                'xturnOverPer'            => $xturnOverPer,
                //'amountRecettesPer'       => $amountRecettesPer,
                //'xamountRecettesPer'      => $xamountRecettesPer,
                //'expensesAmountPer'       => $expensesAmountPer,
                //'xexpensesPer'            => $xexpensesPer,
                //'expensesPer'             => $expensesPer,
                'billNb'                  => $billNb,
                'quoteNb'                 => $quoteNb,
                'purchaseNb'              => $purchaseNb,
                'converted'               => $converted,
                'per'                     => $per,
                'bestSellingProducts'     => $bestSellingProducts,
                'bestSellingProdCategory' => (array)$bestSellingProdCategory,
                'nbProductsSold'          => $nbProductsSold,
                'outstandingDebt'         => $outstandingDebt,
                'outstandingClaim'        => $outstandingClaim,
                'nbCustomer'              => $nbCustomer,
                'nbNewCustomer'          => $nbNewCustomer[0]['nbNewCustomer'],
            ], 200);
        }
        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 403);
    }
}
