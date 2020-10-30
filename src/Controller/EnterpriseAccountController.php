<?php

namespace App\Controller;

use DateTime;
use App\Entity\Enterprise;
use Cocur\Slugify\Slugify;
use App\Form\EnterpriseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class EnterpriseAccountController extends AbstractController
{
    /**
     * @Route("/enterprise/{id<\d+>}/account/profile", name="enterprise_account_profile")
     * 
     *  @Security( "is_granted('ROLE_ADMIN') and enterprise === user.getEnterprise() " )
     */
    public function profile(Enterprise $enterprise, EntityManagerInterface $manager, Request $request)
    {

        $currentMonth = new DateTime('now');
        $dateProto = '%' . $currentMonth->format('Y-m') . '%';
        $types   = ['bill', 'quote', 'purchaseorder'];
        $sheetNb = [];
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
                    'entId'   => $enterprise->getId(),
                    'type_'   => $type,
                    'dat'     => $dateProto,
                ))
                ->getResult();
        }

        $billNb     = $sheetNb['bill'][0]['sheetNb'];
        $quoteNb    = $sheetNb['quote'][0]['sheetNb'];
        $purchaseNb = $sheetNb['purchaseorder'][0]['sheetNb'];
        //$totalDoc = $billNb + $quoteNb + $purchaseNb;
        // $totalDoc = intval($docs[0]['NbDoc']);
        //dump($totalDoc);

        $lastLogo = $enterprise->getLogo();
        $filesystem = new Filesystem();
        $slugify = new Slugify();

        //  instancier un form externe
        $form = $this->createForm(EnterpriseType::class, $enterprise, [
            //'entId'       => $this->getUser()->getEnterprise()->getId(),

        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // @var UploadedFile $logoFile 
            $logoFile = $form->get('logo')->getData();

            // this condition is needed because the 'logo' field is not required
            // so the Image file must be processed only when a file is uploaded
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                // Move the file to the directory where logos are stored
                try {
                    $logoFile->move(
                        $this->getParameter('logo_directory'),
                        $newFilename
                    );
                    $path = $this->getParameter('logo_directory') . '/' . $lastLogo;
                    if ($lastLogo != NULL) $filesystem->remove($path);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $enterprise->setLogo($newFilename);
            }


            $manager->persist($enterprise);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les Modifications du profil ont été enregistrées avec succès !."
            );
        }
        return $this->render('enterprise_account/profile.html.twig', [
            'enterprise' => $enterprise,
            //'totalDoc'   => $totalDoc,
            'fv'         => $billNb,
            'fa'         => $purchaseNb,
            'qte'        => $quoteNb,
            'form'       => $form->createView(),
        ]);
    }
}
