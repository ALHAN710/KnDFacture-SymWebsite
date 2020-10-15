<?php

namespace App\Controller;

use DateTime;
use App\Repository\EnterpriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EnterpriseDashbordController extends ApplicationController
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
            $startDate = new DateTime('2020-10-01 00:00:00 ');
            $endDate = new DateTime('2020-10-09 23:59:59');
            $turnOverPer = $manager->createQuery("SELECT SUBSTRING(cms.createdAt, 1, 13) AS jour, SUM(cms.advancePayment) AS amount
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
                ->getResult();
            //dump($turnOverPer);

            $bestSellingProducts = $manager->createQuery("SELECT cmsi.designation AS designation, SUM(cmsi.quantity) AS totalSale
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
                ->getResult();
            //dump($bestSellingProducts);

            $nbProductsSold = $manager->createQuery("SELECT COUNT(DISTINCT cmsi.designation) AS Designation
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
                ->getResult();
            //dump($nbProductsSold);
            // dd($tmp);
            // dump(gettype(floatval($billCompleted[0]['TotalPayment'])));
            // dd(floatval($billPartial[0]['advanceTotal']));

            return $this->render('enterprise_dashbord/index.html.twig', []);
        }
    }

    /**
     * Permet la MAJ du tableau des mouvements de stock
     * 
     * @Route("/enterprise/dashboard/update/", name="enterprise_dash_update") 
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
            $billPaid = null;
            $billPartial = null;
            $purchaseOrderPaid = null;
            $purchaseOrderPartial = null;
            $turnOver = 0.0;
            $expenses = 0.0;
            $outstandingClaim = 0.0;
            $outstandingDebt = 0.0;
            $turnOverPer = null;
            $expensesPer = null;
            $billNb = 0;
            $quoteNb = 0;
            $purchaseNb = 0;
            $converted = 0;
            $per        = '';
            $xturnOverPer = [];
            $turnOverAmountPer = [];
            $xexpensesPer = [];
            $expensesAmountPer = [];

            $startDate = new DateTime($paramJSON['startDate']);
            $endDate = new DateTime($paramJSON['endDate']);

            $interval = $startDate->diff($endDate);

            if ($interval->days == 0) {
                $endDate_ = $endDate->format('Y-m-d');
                $endDate_ = '%' . $endDate_ . '%';
                $per = 'hour';
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

                $bills = $manager->createQuery("SELECT cms AS CommercialSheet
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt LIKE :dat                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'bill',
                    ))
                    ->getResult();

                $billPartial = $manager->createQuery("SELECT cms.advancePayment AS CommercialSheet 
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND cms.completedStatus = 0
                                            AND cms.deliveryStatus = 0
                                            AND cms.advancePayment > 0
                                            AND e.id = :entId
                                            AND cms.createdAt LIKE :dat                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'bill',
                    ))
                    ->getResult();

                $tmp       = $billPaid[0]['paidTotal'] == null ? 0 : floatval($billPaid[0]['paidTotal']);
                $turnOver += $tmp;
                $tmp       = $billPartial[0]['advanceTotal'] == null ? 0 : floatval($billPartial[0]['advanceTotal']);
                $turnOver += $tmp;

                $purchaseOrderPaid = $manager->createQuery("SELECT SUM(cms.advancePayment) AS paidTotal
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.deliveryStatus = 1)
                                            AND e.id = :entId
                                            AND cms.deliverAt LIKE :dat                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();

                $purchaseOrderPartial = $manager->createQuery("SELECT SUM(cms.advancePayment) AS advanceTotal 
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND cms.completedStatus = 0
                                            AND cms.paymentStatus = 0
                                            AND cms.advancePayment > 0
                                            AND e.id = :entId
                                            AND cms.createdAt LIKE :dat                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'dat'     => $endDate_,
                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();

                $tmp       = $purchaseOrderPaid[0]['paidTotal'] == null ? 0 : floatval($purchaseOrderPaid[0]['paidTotal']);
                $expenses += $tmp;
                $tmp       = $purchaseOrderPartial[0]['advanceTotal'] == null ? 0 : floatval($purchaseOrderPartial[0]['advanceTotal']);
                $expenses += $tmp;

                // // $endDate = '%2020-10-09%';
                // $endDate_ = $endDate->format('Y-m-d');
                // $endDate_ = '%' . $endDate_ . '%';
                $turnOverPer = $manager->createQuery("SELECT SUBSTRING(cms.createdAt, 1, 13) AS jour, SUM(cms.advancePayment) AS amount
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
                        'endDate' => $endDate_,
                        'type_'   => 'bill',
                    ))
                    ->getResult();

                $expensesPer = $manager->createQuery("SELECT SUBSTRING(cms.createdAt, 1, 13) AS jour, SUM(cms.advancePayment) AS amount
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
                        'endDate' => $endDate_,
                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();
            } else {
                $per = 'days';
                $startDate = new DateTime($paramJSON['startDate'] . ' 00:00:00');
                $endDate = new DateTime($paramJSON['endDate'] . ' 23:59:59');

                $billPaid = $manager->createQuery("SELECT SUM(cms.advancePayment) AS paidTotal
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.paymentStatus = 1)
                                            AND e.id = :entId
                                            AND cms.payAt >= :startDate                                                                                  
                                            AND cms.payAt <= :endDate                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate' => $endDate->format('Y-m-d H:m:i'),
                        'type_'   => 'bill',
                    ))
                    ->getResult();

                $billPartial = $manager->createQuery("SELECT SUM(cms.advancePayment) AS advanceTotal 
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND cms.completedStatus = 0
                                            AND cms.paymentStatus = 0
                                            AND cms.advancePayment > 0
                                            AND e.id = :entId
                                            AND cms.createdAt >= :startDate                                                                                  
                                            AND cms.createdAt <= :endDate                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate' => $endDate->format('Y-m-d H:m:i'),

                        'type_'   => 'bill',
                    ))
                    ->getResult();

                $purchaseOrderPaid = $manager->createQuery("SELECT SUM(cms.advancePayment) AS paidTotal
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND (cms.completedStatus = 1 OR cms.paymentStatus = 1) 
                                            AND e.id = :entId
                                            AND cms.payAt >= :startDate                                                                                  
                                            AND cms.payAt <= :endDate                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate' => $endDate->format('Y-m-d H:m:i'),
                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();

                $purchaseOrderPartial = $manager->createQuery("SELECT SUM(cms.advancePayment) AS advanceTotal 
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND cms.completedStatus = 0
                                            AND cms.paymentStatus = 0
                                            AND cms.advancePayment > 0
                                            AND e.id = :entId
                                            AND cms.createdAt >= :startDate                                                                                  
                                            AND cms.createdAt <= :endDate                                                                                  
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate->format('Y-m-d H:m:i'),
                        'endDate' => $endDate->format('Y-m-d H:m:i'),

                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();


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

                $tmp       = $billPaid[0]['paidTotal'] == null ? 0 : floatval($billPaid[0]['paidTotal']);
                $turnOver += $tmp;
                $tmp       = $billPartial[0]['advanceTotal'] == null ? 0 : floatval($billPartial[0]['advanceTotal']);
                $turnOver += $tmp;
                $tmp       = $purchaseOrderPaid[0]['paidTotal'] == null ? 0 : floatval($purchaseOrderPaid[0]['paidTotal']);
                $expenses += $tmp;
                $tmp       = $purchaseOrderPartial[0]['advanceTotal'] == null ? 0 : floatval($purchaseOrderPartial[0]['advanceTotal']);
                $expenses += $tmp;

                $turnOverPer = $manager->createQuery("SELECT SUBSTRING(cms.createdAt, 1, 10) AS jour, SUM(cms.advancePayment) AS amount
                                        FROM App\Entity\CommercialSheet cms
                                        LEFT JOIN cms.user u 
                                        JOIN u.enterprise e
                                        WHERE cms.type = :type_
                                        AND (cms.completedStatus = 1 OR cms.paymentStatus = 1)
                                        AND e.id = :entId
                                        AND cms.createdAt >= :startDate                                                                                  
                                        AND cms.createdAt <= :endDate 
                                        GROUP BY jour
                                        ORDER BY jour ASC                                                                        
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'type_'   => 'bill',
                    ))
                    ->getResult();

                $expensesPer = $manager->createQuery("SELECT SUBSTRING(cms.createdAt, 1, 10) AS jour, SUM(cms.advancePayment) AS amount
                                        FROM App\Entity\CommercialSheet cms
                                        LEFT JOIN cms.user u 
                                        JOIN u.enterprise e
                                        WHERE cms.type = :type_
                                        AND (cms.completedStatus = 1 OR cms.paymentStatus = 1)
                                        AND e.id = :entId
                                        AND cms.createdAt >= :startDate                                                                                  
                                        AND cms.createdAt <= :endDate 
                                        GROUP BY jour
                                        ORDER BY jour ASC
                                                                                
                                        ")
                    ->setParameters(array(
                        'entId'   => $this->getUser()->getEnterprise()->getId(),
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'type_'   => 'purchaseorder',
                    ))
                    ->getResult();
            }

            $billPaymentOnpending = $manager->createQuery("SELECT cms AS paymentOnPending
                                            FROM App\Entity\CommercialSheet cms
                                            LEFT JOIN cms.user u 
                                            JOIN u.enterprise e
                                            WHERE cms.type = :type_
                                            AND cms.paymentStatus = 0
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
                                            AND cms.paymentStatus = 0
                                            AND e.id = :entId
                                                                                                                              
                                        ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                    'type_'   => 'purchaseorder',
                ))
                ->getResult();
            // dump($billPaymentOnpending);
            $outstandingClaim = 0.0;
            foreach ($billPaymentOnpending as $commercialSheet) {
                $outstandingClaim += $commercialSheet['paymentOnPending']->getAmountTTC();
            }

            $outstandingDebt = 0.0;
            foreach ($purchaseOrderPaymentOnpending as $commercialSheet) {
                $outstandingDebt += $commercialSheet['paymentOnPending']->getAmountTTC();
            }

            $bestSellingProducts = $manager->createQuery("SELECT cmsi.designation AS designation,
                                            cmsi.reference AS ref,cmsi.pu AS pu, SUM(cmsi.pu*cmsi.quantity) AS amount,
                                            SUM(cmsi.quantity) AS totalSale
                                            FROM App\Entity\CommercialSheetItem cmsi
                                            JOIN cmsi.commercialSheet cms
                                            JOIN cms.user u
                                            JOIN u.enterprise e
                                            WHERE cms.type = 'bill'
                                            AND cms.paymentStatus = 1
                                            AND cmsi.itemOfferType != 'Simple'
                                            AND e.id = :entId
                                            GROUP BY designation, ref, pu
                                            ORDER BY totalSale DESC 
                                                                                                                            
                                        ")
                ->setParameters(array(
                    'entId'   => $this->getUser()->getEnterprise()->getId(),
                ))
                ->setMaxResults(10)
                ->getResult();
            //dump($bestSellingProducts);

            foreach ($turnOverPer as $d) {
                $xturnOverPer[] = $d['jour'];
                $turnOverAmountPer[]   = number_format((float) $d['amount'], 2, '.', '');
            }

            foreach ($expensesPer as $d) {
                $xexpensesPer[] = $d['jour'];
                $expensesAmountPer[]   = number_format((float) $d['amount'], 2, '.', '');
            }
            $nbProductsSold_ = $manager->createQuery("SELECT COUNT(DISTINCT cmsi.designation) AS Designation
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
                ->getResult();
            $nbProductsSold = $nbProductsSold_[0]['Designation'];
            $billNb     = $sheetNb['bill'][0]['sheetNb'];
            $quoteNb    = $sheetNb['quote'][0]['sheetNb'];
            $purchaseNb = $sheetNb['purchaseorder'][0]['sheetNb'];
            $converted  = $convertedQuoteNb[0]['convertQuoteNb'];

            return $this->json([
                'code'                => 200,
                'turnOver'            => $turnOver,
                'expenses'            => $expenses,
                'turnOverAmountPer'   => $turnOverAmountPer,
                'xturnOverPer'        => $xturnOverPer,
                'expensesAmountPer'   => $expensesAmountPer,
                'xexpensesPer'        => $xexpensesPer,
                'expensesPer'         => $expensesPer,
                'billNb'              => $billNb,
                'quoteNb'             => $quoteNb,
                'purchaseNb'          => $purchaseNb,
                'converted'           => $converted,
                'per'                 => $per,
                'bestSellingProducts' => $bestSellingProducts,
                'nbProductsSold'      => $nbProductsSold,
                'outstandingDebt'     => $outstandingDebt,
                'outstandingClaim'    => $outstandingClaim
            ], 200);
        }
        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 403);
    }
}
