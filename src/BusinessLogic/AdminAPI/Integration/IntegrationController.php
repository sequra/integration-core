<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration;

use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationShopNameResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationUIStateResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationVersionResponse;
use SeQura\Core\BusinessLogic\Domain\Version\Exceptions\FailedToRetrieveVersionException;
use SeQura\Core\BusinessLogic\Domain\Version\Services\VersionService;
use SeQura\Core\Infrastructure\Configuration\Configuration;

/**
 * Class IntegrationController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Integration
 */
class IntegrationController
{
    /**
     * @var VersionService
     */
    private $versionService;

    /**
     * @var Configuration
     */
    private $configurationService;

    /**
     * @param VersionService $versionService
     * @param Configuration $configurationService
     */
    public function __construct(VersionService $versionService, Configuration $configurationService)
    {
        $this->versionService = $versionService;
        $this->configurationService = $configurationService;
    }
    /**
     * Gets the UI state for the integration.
     *
     * @return IntegrationUIStateResponse
     */
    public function getUIState(): IntegrationUIStateResponse
    {
        return IntegrationUIStateResponse::onboarding();
    }

    /**
     * Gets the integration version.
     *
     * @return IntegrationVersionResponse
     *
     * @throws FailedToRetrieveVersionException
     */
    public function getVersion(): IntegrationVersionResponse
    {
        return new IntegrationVersionResponse($this->versionService->getVersion());
    }

    /**
     * Gets the integration shop name.
     *
     * @return IntegrationShopNameResponse
     */
    public function getShopName(): IntegrationShopNameResponse
    {
        return new IntegrationShopNameResponse($this->configurationService->getIntegrationName());
    }
}