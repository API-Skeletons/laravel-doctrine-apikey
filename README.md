# Laravel Doctrine ApiKey

[![Build Status](https://github.com/API-Skeletons/laravel-doctrine-apikey/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/API-Skeletons/laravel-doctrine-apikey/actions/workflows/continuous-integration.yml?query=branch%3Amain)
[![Code Coverage](https://codecov.io/gh/API-Skeletons/laravel-doctrine-apikey/branch/main/graphs/badge.svg)](https://codecov.io/gh/API-Skeletons/laravel-doctrine-apikey/branch/main)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2b-blue)](https://img.shields.io/badge/PHP-8.1%2b-blue)
[![Total Downloads](https://poser.pugx.org/api-skeletons/laravel-doctrine-apikey/downloads)](//packagist.org/packages/api-skeletons/laravel-doctrine-apikey)
[![License](https://poser.pugx.org/api-skeletons/laravel-doctrine-apikey/license)](//packagist.org/packages/api-skeletons/laravel-doctrine-apikey)

This repository provides a driver for Doctrine which can be added to an existing entity manager.  
The driver provides a set of entities which enable ApiKey authorization through HTTP middleware.
Scopes are supported!  This was the missing piece of other repositories which catalyzed the creation of this library.

## Installation

Run the following to install this library using [Composer](https://getcomposer.org/):

```bash
composer require api-skeletons/laravel-doctrine-apikey
```

## Quick Start

Add Service Provider to app.php
```php
    'providers' => [
        ...
        ApiSkeletons\Laravel\Doctrine\ApiKey\ServiceProvider::class,
    ],
```

Add the route middleware to Http Kernel
```php
use ApiSkeletons\Laravel\Doctrine\ApiKey\Http\Middleware\AuthorizeApiKey;

$routeMiddleware = [
    ...
    'auth.apikey' => AuthorizeApiKey:class
];
```

Initialize the ApiKey service for your entity manager in `App\Providers\AppServiceProvider`
```php
use ApiSkeletons\Laravel\Doctrine\ApiKey\Service\ApiKeyService;

public function boot()
{
    app(ApiKeyService::class)->init(app('em'));
}
```

Add an API key through the console
```shell
$ php artisan apikey:generate yourapikeyname
```

Add the middleware to a protected route
```php
Route::name('api.resource::fetch')
    ->get('resource', 'ResourceController::fetch')
    ->middleware('auth.apikey');
```

Begin making requests to your ApiKey protected resource using your apikey as a Bearer token in the Authorization header
```sh
Authorization: Bearer {apikey}
```


## Schema

![Screen Shot 2021-12-17 at 12 20 03 AM](https://user-images.githubusercontent.com/493920/146505347-09778bde-9fff-4c46-819d-fbf2e83d3ad2.png)


## Using Scopes

Scopes are permissions for ApiKeys.  They are commonly used in OAuth2 and are less common in ApiKeys.
Create a scope:
```shell
php artisan apikey:scope:generate {name}
```
Security with scopes is applied with the same middleware used to authenticate ApiKeys.
Replace {scopeName} with your scope's name and the middleware will ensure the passed ApiKey has
that scope to continue.
```php
Route::name('api.resource::fetch')
    ->get('resource', 'ResourceController::fetch')
    ->middleware('auth.apikey:{scopeName}');
```


## Access to ApiKey through request attributes

The ApiKey entity which authenticates a request is assigned to the request attributes as
'apikey'.

```php
$apiKey = request()->attributes->get('apikey');
```


## Using foreign keys to ApiKey

Because an ApiKey can be regenerated, there may be no reason to assign multiple
API keys to the same entity.  For instance, if each Customer has a 1:1 with ApiKey
then you can safely disable that key, regenerate it, and so on; never needing to 
assign a new ApiKey.  

To dynamically create a 1:1 relationship between a Customer entity and API key, 
create an event subscriber:

```php
<?php

declare(strict_types=1);

namespace App\ORM\Event\Subscriber;

use ApiSkeletons\Laravel\Doctrine\ApiKey\Entity\ApiKey;
use App\ORM\Entity\Customer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

class ApiKeyEventSubscriber implements
    EventSubscriber
{
    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        // the $metadata is the whole mapping info for this class
        $metadata = $eventArgs->getClassMetadata();

        switch ($metadata->getName()) {
            case Customer::class:
                $metadata->mapOneToOne([
                    'targetEntity' => ApiKey::class,
                    'fieldName' => 'apiKey',
                ]);
                break;
            default:
                break;
        }
    }
}
```


## Event Logging

Admin events are logged when an ApiKey is generated, activated, deactivated, add a scope, and remove a scope.

Access events are logged when the route middleware allows access to a resource.


## Commands

Management of API keys is handled through the command line.  However, full access to all data-creating
functions is available through the Doctrine repositories: ApiKeyRepository and ScopeRepository.

Generate an ApiKey
```shell
php artisan apikey:generate {name}
```

Generate a Scope
```shell
php artisan apikey:scope:generate {name}
```

Assign a Scope to an ApiKey
```shell
php artisan apikey:scope:add {apiKeyName} {scopeName}
```

Deactivate an ApiKey
```shell
php artisan apikey:deactivate {name}
```

Activate an ApiKey
```shell
php artisan apikey:activate {name}
```

Unassign a Scope from an ApiKey
```shell
php artisan apikey:scope:remove {apiKeyName} {scopeName}
```

Regenerate an ApiKey (assign a new Bearer token)
```shell
php artisan apikey:regenerate {name}
```

Delete a Scope
```shell
php artisan apikey:scope:delete {scopeName}
```

Print ApiKey[s]
```shell
php artisan apikey:print {name?} 
```

Print Scope[s]
```shell
php artisan apikey:scope:print {name?} 
```


## Multiple object managers

The metadata included with this repository works fine across multiple object managers.   
The commands included in this repository only work on the default ApiKeyService, so you will need an alternative 
method of maintaining data in the second object manager.  In order
to use multiple object managers you must do some configuration.  Assuming you followed the Quick Start, above,
follow these steps for a second object manager:

Create a new singleton of the ApiKeyService with a different name in `App\Providers\AppServiceProvider`
```php
use ApiSkeletons\Laravel\Doctrine\ApiKey\Service\ApiKeyService;

public function register(): void
{
    $this->app->singleton('ApiKeyService2', static function ($app) {
        return new ApiKeyService();
    });
}
```

Initialize the ApiKey service for the second entity manager in `App\Providers\AppServiceProvider`
```php
public function boot()
{
    app('ApiKeyService2')->init(app('em2'));
}
```

Copy the route middleware to a new class and use dependency injection for the `ApiKeyService2`
```php
$routeMiddleware = [
    ...
    'auth.apikey2' => EditedAuthorizeApiKey:class
];
```

## Inspired By

The repository https://github.com/ejarnutowski/laravel-api-key was the inispiration for this
repository.  It seemed a fine project but did not have unit tests or scopes.
