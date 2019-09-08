<?php

namespace App\Firebase;

use Kreait\Firebase;

class FirebaseService
{
    /** @var string */
    private $serviceAccountJsonPath;

    public function __construct(string $serviceAccountJsonPath)
    {
        $this->serviceAccountJsonPath = $serviceAccountJsonPath;
    }

    public function getFirebase(): Firebase
    {
        try {
            $serviceAccount = Firebase\ServiceAccount::discover();
        } catch (Firebase\Exception\ServiceAccountDiscoveryFailed $e) {
            if (!is_readable($this->serviceAccountJsonPath)) {
                throw new \Exception('Please place the service account json file here: ' . $this->serviceAccountJsonPath);
            }
            $serviceAccount = Firebase\ServiceAccount::fromJsonFile($this->serviceAccountJsonPath);
        }

        return (new Firebase\Factory())->withServiceAccount($serviceAccount)->create();
    }
}
