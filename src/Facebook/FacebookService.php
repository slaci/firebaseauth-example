<?php

namespace App\Facebook;

use Facebook\Facebook;
use Symfony\Component\Routing\RouterInterface;

class FacebookService
{
    /** @var string */
    private $appId;

    /** @var string */
    private $secret;

    /** @var RouterInterface */
    private $router;

    public function __construct(string $facebookAppId, string $facebookSecret, RouterInterface $router)
    {
        $this->appId = $facebookAppId;
        $this->secret = $facebookSecret;
        $this->router = $router;
    }

    public function getFacebook(): Facebook
    {
        return new Facebook([
            'app_id' => $this->appId,
            'app_secret' => $this->secret,
            'default_graph_version' => 'v3.2',
        ]);
    }

    public function getRedirectUrl(): string
    {
        return $this->router->generate('facebook_callback', [], RouterInterface::ABSOLUTE_URL);
    }
}
