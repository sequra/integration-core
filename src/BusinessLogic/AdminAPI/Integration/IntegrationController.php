<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration;

use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationAvailableUIPagesResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationUIStateResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationVersionResponse;
use SeQura\Core\BusinessLogic\Domain\UIState\Exception\InvalidUIPageException;
use SeQura\Core\BusinessLogic\Domain\UIState\Models\UIPages;
use SeQura\Core\BusinessLogic\Domain\UIState\Models\UIPageState;

/**
 * Class IntegrationController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Integration
 */
class IntegrationController
{
    /**
     * Gets available UI pages for the integration.
     *
     * @return IntegrationAvailableUIPagesResponse
     *
     * @throws InvalidUIPageException
     */
    public function getAvailableUIPages(): IntegrationAvailableUIPagesResponse
    {
        return new IntegrationAvailableUIPagesResponse(
            new UIPages([],[])
        );
    }

    /**
     * Gets the UI state for the integration.
     *
     * @return IntegrationUIStateResponse
     */
    public function getUIState(): IntegrationUIStateResponse
    {
        return new IntegrationUIStateResponse(
            new UIPageState('onboarding','')
        );
    }

    /**
     * Gets the integration version.
     *
     * @return IntegrationVersionResponse
     */
    public function getVersion(): IntegrationVersionResponse
    {
        return new IntegrationVersionResponse(
            ''
        );
    }
}
