<?php

declare(strict_types=1);

namespace ApiSkeletons\Laravel\Doctrine\ApiKey\Http\Middleware;

use ApiSkeletons\Laravel\ApiProblem\Facades\ApiProblem;
use ApiSkeletons\Laravel\Doctrine\ApiKey\Service\ApiKeyService;
use Closure;
use Illuminate\Http\Request;

use function substr;

class AuthorizeApiKey
{
    public function __construct(private ApiKeyService $apiKeyService)
    {
    }

    /**
     * Handle request
     */
    public function handle(Request $request, Closure $next, string|null $scope = null): mixed
    {
        $header = $request->header('Authorization');
        if (! $header) {
            return ApiProblem::response('Missing the Authorization header', 401);
        }

        // Remove Bearer from key prefix
        $key = substr($header, 7);

        $apiKey = $this->apiKeyService->isActive($key);
        if ($apiKey) {
            if (! $scope) {
                $this->apiKeyService->logAccessEvent($request, $apiKey);
                $request->attributes->set('apikey', $apiKey);

                return $next($request);
            }

            // If a scope is passed then verify it exists for the key
            if ($this->apiKeyService->hasScope($key, $scope)) {
                $this->apiKeyService->logAccessEvent($request, $apiKey);
                $request->attributes->set('apikey', $apiKey);

                return $next($request);
            }
        }

        return ApiProblem::response('Invalid ApiKey', 401);
    }
}
