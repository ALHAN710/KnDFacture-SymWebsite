<?php

namespace App\Controller;

use DateTime;
use App\Entity\Role;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserEntrepriseController extends AbstractController
{
    /**
     * @Route("/user/enterprise", name="users_entreprise_index")
     */
    public function index(EntityManagerInterface $manager)
    {
        $userRepo = $manager->getRepository('App:User');
        $users = $userRepo->findBy(['enterprise' => $this->getUser()->getEnterprise()]);
        return $this->render('user_entreprise/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Permet de créer un Produit
     *
     * 
     * 
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function edit($user, EntityManagerInterface $manager, Request $request, UserPasswordEncoderInterface $encoder)
    { //@Route("/user/{id<\d+>}/new", name = "user_edit")
        // $user = new User();
        $slugify = new Slugify();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getHash());

            $userRole = new Role();
            $userRole->setTitle($user->getRole());
            $date = new DateTime(date('Y-m-d H:i:s'));
            $user->setCreatedAt($date);


            $manager->persist($userRole);

            $user->setHash($hash)
                ->addUserRole($userRole);

            /** @var UploadedFile $avatarFile */
            $avatarFile = $form->get('avatar')->getData();

            // this condition is needed because the 'avatar' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                // Move the file to the directory where avatars are stored
                try {
                    $avatarFile->move(
                        $this->getParameter('avatar_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setAvatar($newFilename);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "La modification de l'utilisateur <strong> {$user->getFullName()}</strong> a été effectuée avec succès. !"
            );

            return $this->redirectToRoute('users_entreprise_index');
        }

        return $this->render('user_entreprise/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer un Utilisateur
     * 
     * 
     *
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(User $user, EntityManagerInterface $manager)
    { //@Route("/user/{id<\d+>}/delete", name="user_entreprise_delete")
        $_user = $user->getFullName();

        $manager->remove($user);
        $manager->flush();

        $this->addFlash(
            'success',
            "La suppression de l'utilisateur <strong>{$_user}</strong> a été effectuées avec succès !"
        );

        return $this->redirectToRoute("users_entreprise_index");
    }
}
