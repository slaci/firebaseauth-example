<?php

namespace App\Security\UserProvider;

use App\Firebase\FirebaseService;
use App\Security\FirebaseUser;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FirebaseUserProvider implements UserProviderInterface
{
    /** @var FirebaseService */
    private $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function loadUserByUsername($username)
    {
        // handled by \App\Security\Guard\GoogleLoginGuard::getUser()
    }

    /**
     * @param UserInterface|FirebaseUser $user
     * @return UserInterface|void
     */
    public function refreshUser(UserInterface $user)
    {
        $firebase = $this->firebaseService->getFirebase();
        $linkedProviderData = $user->getLinkedProviderData();

        try {
            $firebase->getAuth()->verifyIdToken($linkedProviderData->idToken, false);
        } catch (AuthException | FirebaseException $e) {
            throw new UsernameNotFoundException();
        }

        return $user;
    }

    public function supportsClass($class)
    {
        return FirebaseUser::class === $class;
    }
}
