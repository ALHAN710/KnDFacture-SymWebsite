<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccueilController extends AbstractController
{

    /**
     * Page d'accueil de l'application
     * 
     * @Route("/", name="homepage")
     * 
     */
    public function home(AuthenticationUtils $utils)
    {

        $user = $this->getUser();
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();
        //dump($user->getRoles()[0]);
        if ($user !== NULL) {
            if ($user->getRoles()[0] === 'ROLE_SUPER_ADMIN' || $user->getRoles()[0] === 'ROLE_SELLER') return $this->redirectToRoute('admin_enterprises_index');
            else return $this->redirectToRoute('business_contacts_index', ['type' => 'customer']);
        } else {
            return $this->render('account/login.html.twig', [
                'hasError' => $error !== null,
                'username' => $username
            ]);
        }
    }
}
