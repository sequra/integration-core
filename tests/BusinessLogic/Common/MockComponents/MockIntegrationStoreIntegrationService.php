<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;

/**
 * Class MockIntegrationStoreIntegrationService.
 *
 * @package Common\MockComponents
 */
class MockIntegrationStoreIntegrationService implements StoreIntegrationServiceInterface
{
    /**
     * @var ?URL $webhookUrl
     */
    private $webhookUrl = null;

    /**
     * @var Capability[]
     */
    private $capabilities = [];

    /**
     * @inheritDoc
     */
    public function getWebhookUrl(): URL
    {
        if ($this->webhookUrl) {
            return $this->webhookUrl;
        }

        return new URL('https://example.com');
    }

    /**
     * @inheritDoc
     */
    public function getSupportedCapabilities(): array
    {
        return $this->capabilities;
    }

    /**
     * @param URL $webhookUrl
     *
     * @return void
     */
    public function setMockWebhookUrl(URL $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @param Capability[] $capabilities
     *
     * @return void
     */
    public function setMockCapabilities(array $capabilities): void
    {
        $this->capabilities = $capabilities;
    }
}
