<?php

namespace App\Controller;

use App\Facebook\FacebookService;
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
    public function login(Request $request, \Google_Client $googleClient, FacebookService $facebookService): Response
    {
        $googleClient->setRedirectUri($this->generateUrl('login_check', [], UrlGeneratorInterface::ABSOLUTE_URL));
        $facebook = $facebookService->getFacebook();

        return $this->render('default/login.html.twig', [
            'auth_url' => $googleClient->createAuthUrl(['email', 'profile', 'openid']),
            'facebook_auth_url' => $facebook->getRedirectLoginHelper()->getLoginUrl(
                $facebookService->getRedirectUrl(),
                ['email']
            ),
            'error' => $request->query->get('error'),
        ]);
    }
}
