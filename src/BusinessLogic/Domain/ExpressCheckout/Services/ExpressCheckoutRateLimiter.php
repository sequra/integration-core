<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout\ExpressCheckoutPeerAddressProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout\ExpressCheckoutRateLimitCacheInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout\ExpressCheckoutSessionIdProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Time\ClockInterface;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services
 */
class ExpressCheckoutRateLimiter
{
    public const SESSION_LIMIT = 30;
    public const ADDRESS_LIMIT = 600;
    public const WINDOW_SECONDS = 60;

    private const SESSION_KEY_PREFIX = 'sequra_express_solicit_sid_';
    private const ADDRESS_KEY_PREFIX = 'sequra_express_solicit_ip_';
    private const UNKNOWN_ADDRESS = 'unknown';

    /**
     * @var ExpressCheckoutRateLimitCacheInterface
     */
    protected $cache;

    /**
     * @var ExpressCheckoutSessionIdProviderInterface
     */
    protected $sessionIdProvider;

    /**
     * @var ExpressCheckoutPeerAddressProviderInterface
     */
    protected $peerAddressProvider;

    /**
     * @var ClockInterface
     */
    protected $clock;

    /**
     * @param ExpressCheckoutRateLimitCacheInterface $cache
     * @param ExpressCheckoutSessionIdProviderInterface $sessionIdProvider
     * @param ExpressCheckoutPeerAddressProviderInterface $peerAddressProvider
     * @param ClockInterface $clock
     */
    public function __construct(
        ExpressCheckoutRateLimitCacheInterface $cache,
        ExpressCheckoutSessionIdProviderInterface $sessionIdProvider,
        ExpressCheckoutPeerAddressProviderInterface $peerAddressProvider,
        ClockInterface $clock
    ) {
        $this->cache = $cache;
        $this->sessionIdProvider = $sessionIdProvider;
        $this->peerAddressProvider = $peerAddressProvider;
        $this->clock = $clock;
    }

    /**
     * @return bool True when this call is over budget. Counts the call.
     */
    public function isExceeded(): bool
    {
        if ($this->registerSessionAttempt()) {
            return true;
        }

        return $this->registerAddressAttempt();
    }

    /**
     * @return bool True when the next call would be over budget. Counts nothing.
     */
    public function wouldBeExceeded(): bool
    {
        return $this->isSessionSpent() || $this->isAddressSpent();
    }

    /**
     * @return bool
     */
    private function registerSessionAttempt(): bool
    {
        try {
            $key = $this->sessionKey();

            return $key !== null && $this->register($key, self::SESSION_LIMIT);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function registerAddressAttempt(): bool
    {
        try {
            return $this->register($this->addressKey(), self::ADDRESS_LIMIT);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function isSessionSpent(): bool
    {
        try {
            $key = $this->sessionKey();

            return $key !== null && $this->isSpent($key, self::SESSION_LIMIT);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function isAddressSpent(): bool
    {
        try {
            return $this->isSpent($this->addressKey(), self::ADDRESS_LIMIT);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @return string|null Null when the shopper has no session id.
     */
    private function sessionKey(): ?string
    {
        $sessionId = $this->sessionIdProvider->getSessionId();
        if ($sessionId === null || $sessionId === '') {
            return null;
        }

        return self::SESSION_KEY_PREFIX . \hash('sha256', $sessionId);
    }

    /**
     * @return string
     */
    private function addressKey(): string
    {
        $address = $this->peerAddressProvider->getPeerAddress();
        if ($address === null || $address === '') {
            $address = self::UNKNOWN_ADDRESS;
        }

        return self::ADDRESS_KEY_PREFIX . \hash('sha256', $address);
    }

    /**
     * @param string $key
     * @param int $limit
     *
     * @return bool
     */
    private function register(string $key, int $limit): bool
    {
        $now = $this->clock->now()->getTimestamp();
        $state = $this->openWindow($this->cache->load($key), $now) ?? ['start' => $now, 'count' => 0];
        $state['count']++;
        $this->cache->save($key, (string)\json_encode($state), self::WINDOW_SECONDS);

        return $state['count'] > $limit;
    }

    /**
     * @param string $key
     * @param int $limit
     *
     * @return bool
     */
    private function isSpent(string $key, int $limit): bool
    {
        $state = $this->openWindow($this->cache->load($key), $this->clock->now()->getTimestamp());

        return $state !== null && $state['count'] >= $limit;
    }

    /**
     * @param string|null $raw
     * @param int $now
     *
     * @return array{start: int, count: int}|null The stored window while it is open, or null.
     */
    private function openWindow(?string $raw, int $now): ?array
    {
        if ($raw === null) {
            return null;
        }

        $state = \json_decode($raw, true);
        if (
            !\is_array($state)
            || !\array_key_exists('start', $state)
            || !\array_key_exists('count', $state)
            || !\is_int($state['start'])
            || !\is_int($state['count'])
            || $now - $state['start'] >= self::WINDOW_SECONDS
        ) {
            return null;
        }

        return ['start' => $state['start'], 'count' => $state['count']];
    }
}
