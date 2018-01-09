<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean D19sp <dinho19sp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace D19sp\JWTAuth\Providers;

use D19sp\JWTAuth\JWT;
use D19sp\JWTAuth\Factory;
use D19sp\JWTAuth\JWTAuth;
use D19sp\JWTAuth\Manager;
use D19sp\JWTAuth\JWTGuard;
use D19sp\JWTAuth\Blacklist;
use D19sp\JWTAuth\Http\Parser\Parser;
use D19sp\JWTAuth\Http\Parser\Cookies;
use Illuminate\Support\ServiceProvider;
use D19sp\JWTAuth\Http\Middleware\Check;
use D19sp\JWTAuth\Http\Parser\AuthHeaders;
use D19sp\JWTAuth\Http\Parser\InputSource;
use D19sp\JWTAuth\Http\Parser\QueryString;
use D19sp\JWTAuth\Http\Parser\RouteParams;
use D19sp\JWTAuth\Contracts\Providers\Auth;
use D19sp\JWTAuth\Contracts\Providers\Storage;
use D19sp\JWTAuth\Validators\PayloadValidator;
use D19sp\JWTAuth\Http\Middleware\Authenticate;
use D19sp\JWTAuth\Http\Middleware\RefreshToken;
use D19sp\JWTAuth\Claims\Factory as ClaimFactory;
use D19sp\JWTAuth\Console\JWTGenerateSecretCommand;
use D19sp\JWTAuth\Http\Middleware\AuthenticateAndRenew;
use D19sp\JWTAuth\Contracts\Providers\JWT as JWTContract;

abstract class AbstractServiceProvider extends ServiceProvider
{
    /**
     * The middleware aliases.
     *
     * @var array
     */
    protected $middlewareAliases = [
        'jwt.auth' => Authenticate::class,
        'jwt.check' => Check::class,
        'jwt.refresh' => RefreshToken::class,
        'jwt.renew' => AuthenticateAndRenew::class,
    ];

    /**
     * Boot the service provider.
     *
     * @return void
     */
    abstract public function boot();

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAliases();

        $this->registerJWTProvider();
        $this->registerAuthProvider();
        $this->registerStorageProvider();
        $this->registerJWTBlacklist();

        $this->registerManager();
        $this->registerTokenParser();

        $this->registerJWT();
        $this->registerJWTAuth();
        $this->registerPayloadValidator();
        $this->registerClaimFactory();
        $this->registerPayloadFactory();
        $this->registerJWTCommand();

        $this->commands('dinho19sp.jwt.secret');
    }

    /**
     * Extend Laravel's Auth.
     *
     * @return void
     */
    protected function extendAuthGuard()
    {
        $this->app['auth']->extend('jwt', function ($app, $name, array $config) {
            $guard = new JwtGuard(
                $app['dinho19sp.jwt'],
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }

    /**
     * Bind some aliases.
     *
     * @return void
     */
    protected function registerAliases()
    {
        $this->app->alias('dinho19sp.jwt', JWT::class);
        $this->app->alias('dinho19sp.jwt.auth', JWTAuth::class);
        $this->app->alias('dinho19sp.jwt.provider.jwt', JWTContract::class);
        $this->app->alias('dinho19sp.jwt.provider.auth', Auth::class);
        $this->app->alias('dinho19sp.jwt.provider.storage', Storage::class);
        $this->app->alias('dinho19sp.jwt.manager', Manager::class);
        $this->app->alias('dinho19sp.jwt.blacklist', Blacklist::class);
        $this->app->alias('dinho19sp.jwt.payload.factory', Factory::class);
        $this->app->alias('dinho19sp.jwt.validators.payload', PayloadValidator::class);
    }

    /**
     * Register the bindings for the JSON Web Token provider.
     *
     * @return void
     */
    protected function registerJWTProvider()
    {
        $this->app->singleton('dinho19sp.jwt.provider.jwt', function ($app) {
            $provider = $this->config('providers.jwt');

            return new $provider(
                $this->config('secret'),
                $this->config('algo'),
                $this->config('keys')
            );
        });
    }

    /**
     * Register the bindings for the Auth provider.
     *
     * @return void
     */
    protected function registerAuthProvider()
    {
        $this->app->singleton('dinho19sp.jwt.provider.auth', function () {
            return $this->getConfigInstance('providers.auth');
        });
    }

    /**
     * Register the bindings for the Storage provider.
     *
     * @return void
     */
    protected function registerStorageProvider()
    {
        $this->app->singleton('dinho19sp.jwt.provider.storage', function () {
            return $this->getConfigInstance('providers.storage');
        });
    }

    /**
     * Register the bindings for the JWT Manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('dinho19sp.jwt.manager', function ($app) {
            $instance = new Manager(
                $app['dinho19sp.jwt.provider.jwt'],
                $app['dinho19sp.jwt.blacklist'],
                $app['dinho19sp.jwt.payload.factory']
            );

            return $instance->setBlacklistEnabled((bool) $this->config('blacklist_enabled'))
                            ->setPersistentClaims($this->config('persistent_claims'));
        });
    }

    /**
     * Register the bindings for the Token Parser.
     *
     * @return void
     */
    protected function registerTokenParser()
    {
        $this->app->singleton('dinho19sp.jwt.parser', function ($app) {
            $parser = new Parser(
                $app['request'],
                [
                    new AuthHeaders,
                    new QueryString,
                    new InputSource,
                    new RouteParams,
                    new Cookies,
                ]
            );

            $app->refresh('request', $parser, 'setRequest');

            return $parser;
        });
    }

    /**
     * Register the bindings for the main JWT class.
     *
     * @return void
     */
    protected function registerJWT()
    {
        $this->app->singleton('dinho19sp.jwt', function ($app) {
            return new JWT(
                $app['dinho19sp.jwt.manager'],
                $app['dinho19sp.jwt.parser']
            );
        });
    }

    /**
     * Register the bindings for the main JWTAuth class.
     *
     * @return void
     */
    protected function registerJWTAuth()
    {
        $this->app->singleton('dinho19sp.jwt.auth', function ($app) {
            return new JWTAuth(
                $app['dinho19sp.jwt.manager'],
                $app['dinho19sp.jwt.provider.auth'],
                $app['dinho19sp.jwt.parser']
            );
        });
    }

    /**
     * Register the bindings for the Blacklist.
     *
     * @return void
     */
    protected function registerJWTBlacklist()
    {
        $this->app->singleton('dinho19sp.jwt.blacklist', function ($app) {
            $instance = new Blacklist($app['dinho19sp.jwt.provider.storage']);

            return $instance->setGracePeriod($this->config('blacklist_grace_period'))
                            ->setRefreshTTL($this->config('refresh_ttl'));
        });
    }

    /**
     * Register the bindings for the payload validator.
     *
     * @return void
     */
    protected function registerPayloadValidator()
    {
        $this->app->singleton('dinho19sp.jwt.validators.payload', function () {
            return (new PayloadValidator)
                ->setRefreshTTL($this->config('refresh_ttl'))
                ->setRequiredClaims($this->config('required_claims'));
        });
    }

    /**
     * Register the bindings for the Claim Factory.
     *
     * @return void
     */
    protected function registerClaimFactory()
    {
        $this->app->singleton('dinho19sp.jwt.claim.factory', function ($app) {
            $factory = new ClaimFactory($app['request']);
            $app->refresh('request', $factory, 'setRequest');

            return $factory->setTTL($this->config('ttl'));
        });
    }

    /**
     * Register the bindings for the Payload Factory.
     *
     * @return void
     */
    protected function registerPayloadFactory()
    {
        $this->app->singleton('dinho19sp.jwt.payload.factory', function ($app) {
            return new Factory(
                $app['dinho19sp.jwt.claim.factory'],
                $app['dinho19sp.jwt.validators.payload']
            );
        });
    }

    /**
     * Register the Artisan command.
     *
     * @return void
     */
    protected function registerJWTCommand()
    {
        $this->app->singleton('dinho19sp.jwt.secret', function () {
            return new JWTGenerateSecretCommand;
        });
    }

    /**
     * Helper to get the config values.
     *
     * @param  string  $key
     * @param  string  $default
     *
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return config("jwt.$key", $default);
    }

    /**
     * Get an instantiable configuration instance.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    protected function getConfigInstance($key)
    {
        $instance = $this->config($key);

        if (is_string($instance)) {
            return $this->app->make($instance);
        }

        return $instance;
    }
}
