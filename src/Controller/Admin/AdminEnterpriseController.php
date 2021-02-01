<?php

namespace App\Controller\Admin;

use App\Entity\Enterprise;
use Cocur\Slugify\Slugify;
use App\Form\EnterpriseType;
use App\Entity\BusinessContact;
use App\Entity\CommercialSheet;
use App\Form\AdminEnterpriseType;
use App\Entity\CommercialSheetItem;
use App\Form\AdminSubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminEnterpriseController extends AbstractController
{
    /**
     * @Route("/admin/clients/home", name="admin_enterprises_index")
     */
    public function index(EntityManagerInterface $manager)
    {
        $tarifs = null;
        $subscription = null;
        $subscriptions = $manager->getRepository('App:Subscription')->findAll();
        foreach ($subscriptions as $subscription_) {
            $tarifs['' . $subscription_->getId()] = $subscription_->getTarifs();
            $subscription = $subscription_;
        }
        //dump($tarifs);
        //  instancier un form externe
        $form = $this->createForm(AdminSubscriptionType::class, $subscription);
        //$form->handleRequest($request);

        $enterprises = $manager->getRepository('App:Enterprise')->findAll();
        return $this->render('admin/enterprises_customer/index_enterprises.html.twig', [
            'enterprises' => $enterprises,
            'form'        => $form->createView(),
            'tarifs'      => $tarifs,
        ]);
    }

    /**
     * Permet de créer un Client Entreprise
     *
     * @Route("/enterprise/new", name = "admin_enterprise_create")
     * 
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_SELLER')" )
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    { // @IsGranted("ROLE_SUPER_ADMIN")
        $enterprise = new Enterprise();
        $enterprise->setRegisterBy($this->getUser());

        $tarifs = null;
        $subscriptions = $manager->getRepository('App:Subscription')->findAll();
        foreach ($subscriptions as $subscription) {
            $tarifs['' . $subscription->getId()] = $subscription->getTarifs();
        }
        //dump($tarifs);
        $lastLogo = $enterprise->getLogo();
        $filesystem = new Filesystem();
        $slugify = new Slugify();

        //  instancier un form externe
        $form = $this->createForm(AdminEnterpriseType::class, $enterprise);
        $form->handleRequest($request);
        //dump($site);

        if ($form->isSubmitted() && $form->isValid()) {
            //Création du business contact(correspondant à l'entreprise en cours de création)
            //de l'entreprise KnD Technologie
            /*$businessContact = new BusinessContact();
            $businessContact->setType('customer')
                ->addEnterprise($this->getUser()->getEnterprise())
                ->setSocialReason($enterprise->getSocialReason())
                ->setNiu($enterprise->getNiu())
                ->setRccm($enterprise->getRccm())
                ->setPhoneNumber($enterprise->getPhoneNumber())
                ->setAddress($enterprise->getAddress())
                ->setEmail($enterprise->getEmail());*/

            //Création d'une nouvelle facture
            $commercialSheet = new CommercialSheet();
            $commercialSheet->setEnterprise($enterprise)
                ->setUser($this->getUser())
                ->setDuration($enterprise->getSubscriptionDuration())
                ->setFixReduction(0)
                ->setAdvancePayment(0)
                ->setType('bill');

            //Création de l'article Abonnement pour la facture 
            $commercialSheetItem = new CommercialSheetItem();
            $commercialSheetItem->setDesignation($enterprise->getSubscription()->getName())
                ->setPU($enterprise->getSubscription()->getTarifs()[$enterprise->getSubscriptionDuration()])
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

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($enterprise);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le Client <strong> {$enterprise->getSocialReason()} </strong> a été crée avec succès !"
            );

            return $this->redirectToRoute('admin_enterprises_index');
        }


        return $this->render('admin/enterprises_customer/new.html.twig', [
            'form'   => $form->createView(),
            'tarifs' => $tarifs,
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition d'un client Entreprise
     *
     * @Route("/admin/enterprise/{id<\d+>}/edit", name="admin_enterprise_edit")
     * 
     * @return Response
     */
    public function edit(Enterprise $enterprise, Request $request, EntityManagerInterface $manager)
    { //@Security("is_granted('ROLE_SUPER_ADMIN')", message = "Vous n'avez pas le droit d'accéder à cette ressource")
        //@IsGranted("ROLE_SUPER_ADMIN")

        /*$tarifs = null;
        $subscriptions = $manager->getRepository('App:Subscription')->findAll();
        foreach ($subscriptions as $subscription) {
            $tarifs['' . $subscription->getId()] = $subscription->getTarifs();
        }*/
        //  instancier un form externe
        $form = $this->createForm(AdminEnterpriseType::class, $enterprise, ['isEdit' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($enterprise);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications du Client <strong> {$enterprise->getSocialReason()} </strong> ont été sauvegardées avec succès !"
            );

            return $this->redirectToRoute('admin_enterprises_index');
        }

        return $this->render('admin/enterprises_customer/edit.html.twig', [
            'form'         => $form->createView(),
            'enterprise'   => $enterprise,
            //'tarifs'       => $tarifs,
        ]);
    }

    /**
     * Permet de supprimer un Client
     * 
     * @Route("/admin/enterprise/{id<\d+>}/delete", name="admin_enterprise_delete")
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     * 
     * @param Enterprise $enterprise
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Enterprise $enterprise, EntityManagerInterface $manager)
    {
        $manager->remove($enterprise);
        $manager->flush();

        $this->addFlash(
            'success',
            "La suppression du Client <strong> {$enterprise->getSocialReason()} </strong> a été effectuée avec succès !"
        );

        return $this->redirectToRoute("admin_enterprises_index");
    }
}
