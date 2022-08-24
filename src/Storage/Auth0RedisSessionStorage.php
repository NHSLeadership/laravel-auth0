<?php

declare(strict_types=1);

namespace Auth0\Laravel\Storage;

use Auth0\SDK\Contract\StoreInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class Auth0RedisSessionStorage implements StoreInterface
{
    private string $prefix;

    public function __construct(string $prefix = 'auth0-redis-session-storage')
    {
        $this->prefix = Str::kebab(Str::lower($prefix));
    }

    /**
     * Set a value on the store
     *
     * @param string $key Key to set.
     * @param mixed $value Value to set.
     */
    public function set(string $key, $value): void
    {
        Redis::set($this->getKeyName($key), $value);
    }

    /**
     * Get a value from the store by a given key.
     *
     * @param string $key Key to get.
     * @param mixed $default Return value if key not found.
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if ($value = Redis::get($this->getKeyName($key))) return $value;

        return $default;
    }

    /**
     * Remove a value from the store
     *
     * @param string $key Key to delete.
     */
    public function delete(string $key): void
    {
        Redis::delete($this->getKeyName($key));
    }

    /**
     * Remove all stored values
     */
    public function purge(): void
    {
        $keys = Redis::keys("$this->prefix:*");

        foreach ($keys as $key) {
            Redis::delete($key);
        }
    }

    /**
     * Defer saving state changes to destination to improve performance during blocks of changes.
     */
    public function defer(bool $deferring): void
    {
        return;
    }

    private function getKeyName(string $key)
    {
        return "$this->prefix:$key";
    }
}
