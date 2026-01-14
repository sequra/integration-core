<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;

/**
 * Interface StoreIntegrationServiceInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration
 */
interface StoreIntegrationServiceInterface
{
    /**
     * Returns webhook url for integration.
     *
     * @return URL
     */
    public function getWebhookUrl(): URL;

    /**
     * Returns an array of supported capabilities.
     *
     * @return Capability[]
     */
    public function getSupportedCapabilities(): array;
}
