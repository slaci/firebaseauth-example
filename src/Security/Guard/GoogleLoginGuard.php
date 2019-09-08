<?php

namespace App\Security\Guard;

use App\Firebase\FirebaseService;
use App\Security\FirebaseUser;
use Kreait\Firebase;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Value\Provider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class GoogleLoginGuard extends AbstractGuardAuthenticator
{
    /** @var \Google_Client */
    private $googleClient;

    /** @var RouterInterface */
    private $router;

    /** @var FirebaseService */
    private $firebaseService;

    public function __construct(\Google_Client $googleClient, FirebaseService $firebaseService, RouterInterface $router)
    {
        $this->googleClient = $googleClient;
        $this->firebaseService = $firebaseService;
        $this->router = $router;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('login'));
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'login_check';
    }

    public function getCredentials(Request $request)
    {
        $error = $request->query->get('error');
        $code = $request->query->get('code');

        return [
            'code' => $code,
            'error' => $error,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!empty($credentials['error'])) {
            throw new CustomUserMessageAuthenticationException($credentials['error']);
        }

        if (empty($credentials['code'])) {
            throw new UsernameNotFoundException();
        }

        $this->googleClient->setRedirectUri($this->router->generate('login_check', [], UrlGeneratorInterface::ABSOLUTE_URL));
        $oauthToken = $this->googleClient->fetchAccessTokenWithAuthCode($credentials['code']);
        if (isset($oauthToken['error'])) {
            throw new CustomUserMessageAuthenticationException($oauthToken['error'] . ': ' . $oauthToken['error_description']);
        }

        $linkProviderData = $this->authFirebase($oauthToken);

        return new FirebaseUser($linkProviderData);
    }

    private function authFirebase(array $googleOauthToken): Firebase\Auth\LinkedProviderData
    {
        $firebase = $this->firebaseService->getFirebase();
        try {
            return $firebase->getAuth()->linkProviderThroughIdToken(Provider::GOOGLE, $googleOauthToken['id_token']);
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
