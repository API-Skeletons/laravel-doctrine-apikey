<?php

declare(strict_types=1);

namespace ApiSkeletons\Laravel\Doctrine\ApiKey\Console\Command;

use ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\ApiKey;
use ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\Scope;
use ApiSkeletons\Laravel\Doctrine\ApiKey\Service\ApiKeyService;
use Illuminate\Console\Command as IlluminateCommand;

use function count;
use function implode;

abstract class Command extends IlluminateCommand
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(protected ApiKeyService $apiKeyService)
    {
        parent::__construct();
    }

    /** @param ApiKey[] $apiKeys */
    protected function printApiKeys(array $apiKeys): void
    {
        $headers = ['name', 'api_key', 'status', 'scopes'];

        $rows = [];
        foreach ($apiKeys as $apiKey) {
            $scopeNames = [];
            foreach ($apiKey->getScopes() as $s) {
                $scopeNames[] = $s->getName();
            }

            $rows[] = [
                $apiKey->getName(),
                $apiKey->getApiKey(),
                $apiKey->getIsActive() ? 'active' : 'deactivated',
                implode(',', $scopeNames),
            ];
        }

        $this->table($headers, $rows);
    }

    /** @param Scope[] $scopes */
    protected function printScopes(array $scopes): void
    {
        $headers = ['name', 'apikey count'];

        $rows = [];
        foreach ($scopes as $scope) {
            $rows[] = [
                $scope->getName(),
                count($scope->getApiKeys()),
            ];
        }

        $this->table($headers, $rows);
    }
}
