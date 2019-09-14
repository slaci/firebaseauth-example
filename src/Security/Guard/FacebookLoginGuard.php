<?php

namespace App\Security\Guard;

use App\Facebook\FacebookService;
use App\Firebase\FirebaseService;
use App\Security\FirebaseUser;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Value\Provider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class FacebookLoginGuard extends AbstractGuardAuthenticator
{
    /** @var FacebookService */
    private $facebookService;

    /** @var RouterInterface */
    private $router;

    /** @var FirebaseService */
    private $firebaseService;

    /** @var Security */
    private $security;

    public function __construct(FacebookService $facebookService, FirebaseService $firebaseService, RouterInterface $router, Security $security)
    {
        $this->facebookService = $facebookService;
        $this->firebaseService = $firebaseService;
        $this->router = $router;
        $this->security = $security;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('login'));
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'facebook_callback' && null === $this->security->getUser();
    }

    public function getCredentials(Request $request)
    {
        return [
            'code' => $request->query->get('code'),
            'error' => $request->query->get('error_message'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $firebase = $this->firebaseService->getFirebase();
        $facebook = $this->facebookService->getFacebook();
        $helper = $facebook->getRedirectLoginHelper();

        if (!empty($credentials['error'])) {
            throw new CustomUserMessageAuthenticationException($credentials['error']);
        }

        try {
            $accessToken = $helper->getAccessToken($this->facebookService->getRedirectUrl());
        } catch(FacebookResponseException | FacebookSDKException $e) {
            throw new CustomUserMessageAuthenticationException($e->getMessage());
        }

        try {
            $linkedProviderData = $firebase->getAuth()->linkProviderThroughAccessToken(Provider::FACEBOOK, $accessToken);

            return new FirebaseUser($linkedProviderData);
        } catch (AuthException | FirebaseException $e) {
            throw new CustomUserMessageAuthenticationException('Firebase exception: ' . $e->getMessage());
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse($this->router->generate('login', ['error' => $exception->getMessage()]));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->router->generate('homepage'));
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
