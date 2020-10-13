<?php

namespace App\Controller;

use Faker;
use DateTime;
use App\Entity\Role;
use App\Entity\User;
use App\Form\AccountType;
use Cocur\Slugify\Slugify;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends ApplicationController
{
    /**
     * Permet d'afficher et de gérer le formulaire de connexion
     * 
     * @Route("/login", name="account_login", methods={"GET", "POST"})
     */
    public function login(AuthenticationUtils $utils)
    {
        // get the login error if there is one
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        return $this->render('account/login.html.twig', [
            'hasError' => $error !== null,
            'error'    => $error,
            'username' => $username
        ]);
    }

    /**
     * Permet de se déconnecter
     *
     * @Route("/logout", name = "account_logout")
     * 
     * @return void
     */
    public function logout()
    {
    }


    /**
     * Permet d'afficher le formulaire d'inscription
     * 
     * @Route("/register", name="account_register")
     * 
     * @IsGranted("ROLE_ADMIN")
     *
     * @return Response
     */
    public function create(EntityManagerInterface $manager, InventoryRepository $inventoryRepo, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $user->setEnterprise($this->getUser()->getEnterprise());
        $slugify = new Slugify();
        $form = $this->createForm(RegistrationType::class, $user);
        $inventories = $inventoryRepo->findAll();
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
                "Le Compte utilisateur <strong> {$user->getFirstName()}</strong> a été crée avec succès. !"
            );

            return $this->redirectToRoute('users_entreprise_index');
        }

        return $this->render('account/new.html.twig', [
            'form' => $form->createView(),
            'inventories' => $inventories,
        ]);
    }

    /**
     * Permet d'afficher et de traiter le formulaire de modification de profil
     *
     * @Route("/account/profile", name="account_profile")
     * 
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function profile(Request $request, InventoryRepository $inventoryRepo, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $lastAvatar = $user->getAvatar();
        //$lastLogo = $user->getEnterpriseLogo();
        $inventories = $inventoryRepo->findAll();
        $filesystem = new Filesystem();

        $slugify = new Slugify();
        //dump($this->getParameter('avatar_directory'));
        $form = $this->createForm(AccountType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // @var UploadedFile $avatarFile 
            $avatarFile = $form->get('avatar')->getData();

            // this condition is needed because the 'avatar' field is not required
            // so the Image file must be processed only when a file is uploaded
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
                    $path = $this->getParameter('avatar_directory') . '/' . $lastAvatar;
                    if ($lastAvatar != NULL) $filesystem->remove($path);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setAvatar($newFilename);
            }


            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Profile changes have been successfully saved."
            );
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            //'inventories' => $inventories,
        ]);
    }

    /**
     * Permet de modifier le compte d'un utilisateur
     *
     * @Route("/user/{id<\d+>}/edit", name = "user_edit")
     * 
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function edit($user, EntityManagerInterface $manager, Request $request, UserPasswordEncoderInterface $encoder)
    {
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
     * @Route("/user/{id<\d+>}/delete", name="user_delete")
     * 
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(User $user, EntityManagerInterface $manager)
    {
        $_user = $user->getFullName();

        $manager->remove($user);
        $manager->flush();

        $this->addFlash(
            'success',
            "La suppression de l'utilisateur <strong>{$_user}</strong> a été effectuées avec succès !"
        );

        return $this->redirectToRoute("users_entreprise_index");
    }

    /**
     * Permet de modifier le mot de passe
     * 
     * @Route("/account/password-update", name="account_password")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function updatePassword(Request $request, InventoryRepository $inventoryRepo, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $passwordUpdate = new PasswordUpdate();
        $inventories = $inventoryRepo->findAll();
        $user = $this->getUser();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //1. Vérifier que le oldpassword soit le même que celui de l'user
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getHash())) {
                //Gérer l'erreur
                $form->get('oldPassword')->addError(new FormError("Le mot de passe saisi n'est pas votre mot de passe actuel"));
            } else {
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->encodePassword($user, $newPassword);

                $user->setHash($hash);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Votre mot de passe a bien été modifié"
                );

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            //'inventories' => $inventories,
        ]);
    }

    /**
     * Permet d'envoyer un code réinitialisation de mot de passe d'un utilisateur à son adresse email
     * 
     * @Route("/account/recover/password", name="account_recoverpw")
     *
     * @return Response
     */
    public function recoverPassword(InventoryRepository $inventoryRepo)
    {
        $inventories = $inventoryRepo->findAll();
        return $this->render('account/recoverpw.html.twig', [
            //'inventories' => $inventories,
        ]);
    }

    /**
     * Permet de vérifier le code réinitialisation de mot de passe d'un utilisateur
     * 
     * @Route("/account/recover/password/code-verification", name="account_codeverification")
     *
     * @return void
     */
    public function codeVerification(InventoryRepository $inventoryRepo)
    {
        //$inventories = $inventoryRepo->findAll();
        return $this->render('account/codeverification.html.twig', [
            //'inventories' => $inventories,
        ]);
    }

    /**
     * Permet de vérifier si l'adresse email entrer pour la réinitialisation de mot appartient à un utilisateur du site
     *
     * @Route("/account/recover/password/user-verification", name="account_userverification")
     * 
     * @param Request $request
     * @param MailerInterface $mailer
     * @param UserRepository $userRepo
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     * 
     */
    public function userVerification(Request $request, InventoryRepository $inventoryRepo, MailerInterface $mailer, UserRepository $userRepo, EntityManagerInterface $manager): JsonResponse
    {
        $paramJSON = $this->getJSONRequest($request->getContent());
        $inventories = $inventoryRepo->findAll();
        $email = $paramJSON['email'];
        //dump($email);
        $user = $userRepo->findOneBy(['email' => $email]);
        if ($user != null) {
            $status = 200;
            $mess   = 'User exists';
            $faker = Faker\Factory::create('fr_FR');
            $codeVerification = $faker->randomNumber($nbDigits = 5, $strict = false);
            $user->setVerificationcode($codeVerification)
                ->setVerified(false);
            $manager->persist($user);
            $manager->flush();
            $code = 'LBF OSM-' . $codeVerification . $user->getId();
            //dump($code);
            $object = "PASSWORD RESET";
            $message = 'Your verification code is ' . $code;
            $message += "We heard that you lost your LBF password. Sorry about that !

But don’t worry! You can use the following code to reset your password: " . $code . "

Thanks,
The LBF Team";
            $this->sendEmail($mailer, $email, $object, $message);
        } else if ($paramJSON['codeVerif'] != null) {
            $Verificationcode = $paramJSON['codeVerif'];
            $id = substr($Verificationcode, 5);
            $Verificationcode = substr($Verificationcode, 0, 5);
            $user = $userRepo->findOneBy(['id' => $id]);
            //dump($id);
            //dump($Verificationcode);
            //dump($user);
            if ($user != null && $user->getVerified() == false) {
                $userCode = $user->getVerificationcode();
                if ($userCode == $Verificationcode) {
                    $status = 200;
                    $mess   = $id;
                }
            } else {
                $status = 403;
                $mess   = $Verificationcode;
            }
        } else if ($paramJSON['codeVerif'] == null) {
            $status = 403;
            $mess   = "User don't exists";
        }
        //$status = 200;
        //$mess = 'received email : ' . $email;
        return $this->json(
            [
                'code'    => $status,
                'message' => $mess,
            ],
            200
        );
    }

    /**
     * Permet de vérifier si l'adresse email entrer pour la réinitialisation de mot appartient à un utilisateur du site
     *
     * @Route("/account/recover/password/reset", name="account_passwordReset")
     * 
     * @param Request $request
     * @param UserRepository $userRepo
     * @param EntityManagerInterface $manager
     * @return Response
     * 
     */
    public function passwordReset(Request $request, InventoryRepository $inventoryRepo, UserRepository $userRepo, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $passwordUpdate = new PasswordUpdate();
        $inventories = $inventoryRepo->findAll();
        $user = $this->getUser();
        $id = $request->query->get('d');
        //dump($id);
        $user = $userRepo->findOneBy(['id' => $id]);
        //dump($user);

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getVerified() == false) {
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->encodePassword($user, $newPassword);

                $user->setHash($hash)
                    ->setVerificationcode("")
                    ->setVerified(true);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Your password has been changed"
                );

                return $this->redirectToRoute('account_login');
            } else {
                $this->addFlash(
                    'danger',
                    "Unauthorized Modification"
                );
            }
        }

        return $this->render('account/resetpassword.html.twig', [
            'form' => $form->createView(),
            //'inventories' => $inventories,
        ]);
    }
}
