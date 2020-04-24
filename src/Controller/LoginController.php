<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUserName = $utils->getLastUsername();
        
        return $this->render('login.html.twig', [
            'error' => $error,
            'last_username' => $lastUserName
        ]);    
    }

    /**
     * @Route("/", name="home")
     */

    public function home() {

        return $this->redirectToRoute('login');
    }
}
