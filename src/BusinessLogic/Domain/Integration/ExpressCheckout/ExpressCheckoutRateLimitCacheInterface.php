<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout
 */
interface ExpressCheckoutRateLimitCacheInterface
{
    /**
     * @param string $key
     *
     * @return string|null The value saved under the key by any web node, or null when absent or expired.
     */
    public function load(string $key): ?string;

    /**
     * @param string $key
     * @param string $value
     * @param int $ttlSeconds
     *
     * @return void The value is visible to every web node until its TTL expires.
     */
    public function save(string $key, string $value, int $ttlSeconds): void;
}
