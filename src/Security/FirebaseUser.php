<?php

namespace App\Security;

use Kreait\Firebase\Auth\LinkedProviderData;
use Kreait\Firebase\Auth\UserRecord;
use Symfony\Component\Security\Core\User\UserInterface;

class FirebaseUser implements UserInterface
{
    /** @var string */
    private $email;

    /** @var UserRecord */
    private $userRecord;

    /** @var LinkedProviderData */
    private $linkedProviderData;

    public function __construct(LinkedProviderData $linkProviderData)
    {
        $this->linkedProviderData = $linkProviderData;
        $this->userRecord = $linkProviderData->userRecord;
        $this->email = $this->userRecord->email;
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
    }

    public function getLinkedProviderData(): LinkedProviderData
    {
        return $this->linkedProviderData;
    }

    public function getUserRecord(): UserRecord
    {
        return $this->userRecord;
    }

    public function getDisplayName(): string
    {
        return $this->getUserRecord()->displayName;
    }
}
