<?php

declare(strict_types=1);

namespace Auth0\Laravel;

use Auth0\Laravel\Storage\Auth0RedisSessionStorage;
use Auth0\SDK\Store\Psr6Store;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

/**
 * Service that provides access to the Auth0 SDK.
 */
final class Auth0 implements \Auth0\Laravel\Contract\Auth0
{
    /**
     * The Laravel-Auth0 SDK version:
     */
    public const VERSION = '7.1.0';

    /**
     * An instance of the Auth0-PHP SDK.
     */
    private ?\Auth0\SDK\Contract\Auth0Interface $sdk = null;

    /**
     * An instance of the Auth0-PHP SDK's SdkConfiguration, which handles configuration state.
     */
    private ?\Auth0\SDK\Configuration\SdkConfiguration $configuration = null;

    /**
     * @inheritdoc
     */
    public function getSdk(): \Auth0\SDK\Contract\Auth0Interface
    {
        if ($this->sdk === null) {
            $this->sdk = new \Auth0\SDK\Auth0($this->getConfiguration());
            $this->setSdkTelemetry();
        }

        return $this->sdk;
    }

    /**
     * @inheritdoc
     */
    public function setSdk(
        \Auth0\SDK\Contract\Auth0Interface $sdk
    ): self {
        $this->sdk = $sdk;
        $this->setSdkTelemetry();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration(): \Auth0\SDK\Configuration\SdkConfiguration
    {
        if ($this->configuration === null) {

            $config = app()->make('config')->get('auth0');

            if (isset($config['useRedisForSessionStorage']) && $config['useRedisForSessionStorage']) {

                $auth0PublicStore = new Auth0RedisSessionStorage();
                $auth0PrivateStore = new Psr16Adapter(app('cache.store'));
                $config['sessionStorage'] = new Psr6Store($auth0PublicStore, $auth0PrivateStore, 'auth0-session-storage');

            }

            $this->configuration = new \Auth0\SDK\Configuration\SdkConfiguration($config);

        }

        return $this->configuration;
    }

    /**
     * @inheritdoc
     */
    public function setConfiguration(
        \Auth0\SDK\Configuration\SdkConfiguration $configuration
    ): self {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getState(): \Auth0\Laravel\Contract\StateInstance
    {
        return app()->make(\Auth0\Laravel\StateInstance::class);
    }

    /**
     * Updates the Auth0 PHP SDK's telemetry to include the correct Laravel markers.
     */
    private function setSdkTelemetry(): self
    {
        \Auth0\SDK\Utility\HttpTelemetry::setEnvProperty('Laravel', app()->version());
        \Auth0\SDK\Utility\HttpTelemetry::setPackage('laravel-auth0', self::VERSION);

        return $this;
    }
}
