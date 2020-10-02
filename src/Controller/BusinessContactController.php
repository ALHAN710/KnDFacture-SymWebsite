<?php

namespace App\Controller;

use App\Entity\BusinessContact;
use App\Form\BusinessContactType;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BusinessContactRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BusinessContactController extends AbstractController
{
    /**
     * @Route("/business/contact/{type<[a-z]+>}", name="business_contacts_index")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     */
    public function index($type, BusinessContactRepository $businessContactRepo, InventoryRepository $inventoryRepo)
    {
        $businessContacts = $businessContactRepo->findBy(['type' => $type]);
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //dump($businesContacts[0]->getDeliveryAddresses());
        return $this->render('business_contact/index_business_contact.html.twig', [
            'businessContacts' => $businessContacts,
            'inventories'      => $inventories,
            'type'             => $type
        ]);
    }

    /**
     * Permet de créer un businessContact
     *
     * @Route("/business/contact/{type<[a-z]+>}/new", name = "business_contact_create")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     * @return Response
     */
    public function create($type, Request $request, EntityManagerInterface $manager, InventoryRepository $inventoryRepo)
    { //
        $businessContact = new BusinessContact();
        $businessContact->setType($type)
            ->addEnterprise($this->getUser()->getEnterprise());

        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        dump($request);
        //  instancier un form externe
        $form = $this->createForm(BusinessContactType::class, $businessContact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*foreach ($site->getSmartMods() as $smartMod) {
                $smartMod->setSite($site);
                $manager->persist($smartMod);
            }*/

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($businessContact);
            $manager->flush();

            $this->addFlash(
                'success',
                "The {$type} <strong> {$businessContact->getSocialReason()} </strong> has been registered successfully !"
            );

            return $this->redirectToRoute('business_contacts_index', ['type' => $type]);
        }


        return $this->render(
            'business_contact/new.html.twig',
            [
                'form'        => $form->createView(),
                'type'        => $type,
                'inventories' => $inventories,
            ]
        );
    }

    /**
     * Permet d'afficher le formulaire d'édition d'un businessContact
     *
     * @Route("/business/contact/{id<\d+>}/edit", name="business_contact_edit")
     * 
     * @IsGranted("ROLE_MANAGER")
     * 
     * @return Response
     */
    public function edit(BusinessContact $businessContact, Request $request, InventoryRepository $inventoryRepo, EntityManagerInterface $manager)
    { //@Security("is_granted('ROLE_MANAGER')", message = "Vous n'avez pas le droit d'accéder à cette ressource")

        //$businessContact = $businessContactRepo->findOneBy(['id' => $id]);
        $inventories = $inventoryRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        //  instancier un form externe
        $form = $this->createForm(BusinessContactType::class, $businessContact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //dump($request);
            //$businessContact->getDeliveryAddress();
            /*foreach ($businessContact->getDeliveryAddresses() as $deliveryAddress) {
                $deliveryAddress->addbusinessContact($businessContact);
                $manager->persist($deliveryAddress);
            }*/

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($businessContact);
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of the businessContact <strong> {$businessContact->getSocialReason()} </strong> have been saved !"
            );

            return $this->redirectToRoute('business_contacts_index', ['type' => $businessContact->getType()]);
        }

        return $this->render('business_contact/edit.html.twig', [
            'form' => $form->createView(),
            'inventories' => $inventories,
            'type' => $businessContact->getType()
        ]);
    }

    /**
     * Permet de supprimer un businessContact
     * 
     * @Route("/business/contact/{id}/delete", name="business_contact_delete")
     *
     * @IsGranted("ROLE_MANAGER")
     * 
     * @param BusinessContact $businessContact
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(BusinessContact $businessContact, EntityManagerInterface $manager)
    {
        $manager->remove($businessContact);
        $manager->flush();

        $this->addFlash(
            'success',
            "The removal of Business Contact <strong> {$businessContact->getFullName()} </strong> has been completed successfully !"
        );

        return $this->redirectToRoute("business_contacts_index", ['type' => $businessContact->getType()]);
    }
}
