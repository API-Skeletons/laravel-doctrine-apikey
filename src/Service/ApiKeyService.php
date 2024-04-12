<?php

declare(strict_types=1);

namespace ApiSkeletons\Laravel\Doctrine\ApiKey\Service;

use ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\AccessEvent;
use ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\ApiKey;
use ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\Scope;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Illuminate\Http\Request;

class ApiKeyService
{
    private EntityManager|null $entityManager = null;

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function init(EntityManager $entityManager): self|bool
    {
        if ($this->entityManager !== null) {
            return false;
        }

        $driver = new XmlDriver(__DIR__ . '/../../config/orm');

        $entityManager
            ->getConfiguration()
            ->getMetadataDriverImpl()
            ->addDriver($driver, 'ApiSkeletons\Laravel\Doctrine\ApiKey\Entity');

        $this->entityManager = $entityManager;

        return $this;
    }

    public function isActive(string $key): ApiKey|bool
    {
        $apiKey = $this->entityManager->getRepository(ApiKey::class)
            ->findOneBy(['api_key' => $key]);

        if (! $apiKey || ! $apiKey->getIsActive()) {
            return false;
        }

        return $apiKey;
    }

    public function hasScope(string $key, string $scopeName): bool
    {
        $apiKey = $this->entityManager->getRepository(ApiKey::class)
            ->findOneBy(['api_key' => $key]);

        $scope = $this->entityManager->getRepository(Scope::class)
            ->findOneBy(['name' => $scopeName]);

        if (! $apiKey || ! $scope) {
            return false;
        }

        $found = false;
        foreach ($apiKey->getScopes() as $s) {
            if ($scope === $s) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    /**
     * Log an access event
     */
    public function logAccessEvent(Request $request, ApiKey $apiKey): void
    {
        $event = (new AccessEvent())
            ->setCreatedAt(new DateTime())
            ->setApiKey($apiKey)
            ->setIpAddress($request->ip())
            ->setUrl($request->fullUrl());

        $apiKey->addAccessEvent($event);

        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }
}
