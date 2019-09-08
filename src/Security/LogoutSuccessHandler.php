<?php

namespace App\Security;

use App\Firebase\FirebaseService;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /** @var FirebaseService */
    private $firebaseService;

    /** @var Security */
    private $security;

    /** @var RouterInterface */
    private $router;

    public function __construct(FirebaseService $firebaseService, Security $security, RouterInterface $router)
    {
        $this->firebaseService = $firebaseService;
        $this->security = $security;
        $this->router = $router;
    }

    public function onLogoutSuccess(Request $request)
    {
        $user = $this->security->getUser();
        if ($user instanceof FirebaseUser) {
            $firebase = $this->firebaseService->getFirebase();

            try {
                $firebase->getAuth()->revokeRefreshTokens($user->getUserRecord()->uid);
            } catch (AuthException | FirebaseException $e) {
            }
        }

        return new RedirectResponse($this->router->generate('homepage'));
    }
}
