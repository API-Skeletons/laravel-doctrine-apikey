# Laravel Doctrine ApiKey

[![Build Status](https://github.com/API-Skeletons/laravel-doctrine-apikey/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/API-Skeletons/laravel-doctrine-apikey/actions/workflows/continuous-integration.yml?query=branch%3Amain)
[![Code Coverage](https://codecov.io/gh/API-Skeletons/laravel-doctrine-apikey/branch/main/graphs/badge.svg)](https://codecov.io/gh/API-Skeletons/laravel-doctrine-apikey/branch/main)
[![PHP Version](https://img.shields.io/badge/PHP-8.0-blue)](https://img.shields.io/badge/PHP-8.0-blue)
[![Total Downloads](https://poser.pugx.org/api-skeletons/laravel-doctrine-apikey/downloads)](//packagist.org/packages/api-skeletons/laravel-doctrine-apikey)
[![License](https://poser.pugx.org/api-skeletons/laravel-doctrine-apikey/license)](//packagist.org/packages/api-skeletons/laravel-doctrine-apikey)

This repository provides a driver for Doctrine which can be added to an existing entity manager.  
The driver provies a set of entities which enable ApiKey authorization through HTTP middleware.
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

Begin making requests to your ApiKey protected resource using your key as a Bearer token in the Authorization header
```sh
Authorization: Bearer {key}
```


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

Delete a Scope
```shell
php artisan apikey:scope:delete {scopeName}
```

Print an ApiKey
```shell
php artisan apikey:print {name} 
```


## Multiple object managers

The metadata included with this repository works fine across multiple object managers.   
The commands included in this repository only work on the default ApiKeyService, so you will need an alternative 
method of maintaining data in the second object manager.  In order
to use multiple object managers you must do some configuration.  Assuming you followed the Quick Start, above,
follow these steps for a second object manager:

Create a new singleton of the ApiKeyService with a differnet name in `App\Providers\AppServiceProvider`
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
use ApiSkeletons\Laravel\Doctrine\ApiKey\Service\ApiKeyService;

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
