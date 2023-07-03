<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration;

use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationUIStateResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationVersionResponse;
use SeQura\Core\BusinessLogic\Domain\Version\Models\Version;

/**
 * Class IntegrationController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Integration
 */
class IntegrationController
{
    /**
     * Gets the UI state for the integration.
     *
     * @return IntegrationUIStateResponse
     */
    public function getUIState(): IntegrationUIStateResponse
    {
        return IntegrationUIStateResponse::dashboard();
    }

    /**
     * Gets the integration version.
     *
     * @return IntegrationVersionResponse
     */
    public function getVersion(): IntegrationVersionResponse
    {
        return new IntegrationVersionResponse(new Version(
            'v1.0.1',
            'v1.0.5',
            'https://logeecom.com/wp-content/uploads/2016/09/logo-white.png'
        ));
    }
}
