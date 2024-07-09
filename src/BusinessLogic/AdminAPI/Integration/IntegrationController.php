<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationShopNameResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationUIStateResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationVersionResponse;
use SeQura\Core\BusinessLogic\Domain\UIState\Services\UIStateService;
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
    protected $versionService;

    /**
     * @var Configuration
     */
    protected $configurationService;

    /**
     * @var UIStateService
     */
    protected $stateService;

    /**
     * @param VersionService $versionService
     * @param Configuration $configurationService
     * @param UIStateService $stateService
     */
    public function __construct(
        VersionService $versionService,
        Configuration $configurationService,
        UIStateService $stateService
    ) {
        $this->versionService = $versionService;
        $this->configurationService = $configurationService;
        $this->stateService = $stateService;
    }

    /**
     * Gets the UI state for the integration.
     *
     * @param bool $useWidgets
     *
     * @return IntegrationUIStateResponse
     *
     * @throws Exception
     */
    public function getUIState(bool $useWidgets = true): IntegrationUIStateResponse
    {
        return $this->stateService->isOnboardingState($useWidgets) ?
            IntegrationUIStateResponse::onboarding() :
            IntegrationUIStateResponse::dashboard();
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
