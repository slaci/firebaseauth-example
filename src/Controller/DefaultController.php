<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, \Google_Client $googleClient): Response
    {
        $googleClient->setRedirectUri($this->generateUrl('login_check', [], UrlGeneratorInterface::ABSOLUTE_URL));

        return $this->render('default/login.html.twig', [
            'auth_url' => $googleClient->createAuthUrl(['email', 'profile', 'openid']),
            'error' => $request->query->get('error'),
        ]);
    }
}
