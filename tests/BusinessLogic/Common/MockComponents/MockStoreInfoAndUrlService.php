<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreInfoServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreUrlProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\StoreInfo;

/**
 * Mock that opts into the lightweight {@see StoreUrlProviderInterface} path
 * alongside the legacy {@see StoreInfoServiceInterface} contract. Used to
 * verify that {@see \SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService::signaturePayload()}
 * prefers the cheap accessor when available and never falls back to
 * {@see StoreInfoServiceInterface::getStoreInfo()} for HMAC purposes.
 *
 * @package Common\MockComponents
 */
class MockStoreInfoAndUrlService implements StoreInfoServiceInterface, StoreUrlProviderInterface
{
    /**
     * @var string
     */
    private $storeUrl;

    /**
     * @var int
     */
    private $getStoreUrlCallCount = 0;

    /**
     * @var int
     */
    private $getStoreInfoCallCount = 0;

    /**
     * @param string $storeUrl
     */
    public function __construct(string $storeUrl)
    {
        $this->storeUrl = $storeUrl;
    }

    /**
     * @inheritDoc
     */
    public function getStoreUrl(): string
    {
        $this->getStoreUrlCallCount++;

        return $this->storeUrl;
    }

    /**
     * @inheritDoc
     */
    public function getStoreInfo(): StoreInfo
    {
        $this->getStoreInfoCallCount++;

        return new StoreInfo(
            'fallback-name',
            $this->storeUrl,
            '',
            '',
            '',
            '',
            '',
            ''
        );
    }

    /**
     * @return int
     */
    public function getStoreUrlCallCount(): int
    {
        return $this->getStoreUrlCallCount;
    }

    /**
     * @return int
     */
    public function getStoreInfoCallCount(): int
    {
        return $this->getStoreInfoCallCount;
    }
}
