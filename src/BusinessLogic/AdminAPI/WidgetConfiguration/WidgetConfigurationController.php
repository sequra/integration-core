<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration;

use SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Requests\WidgetConfigurationRequest;
use SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Responses\SuccessfulWidgetConfigurationResponse;
use SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Responses\WidgetConfigurationResponse;
use SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Responses\WidgetConfiguratorResponse;
use SeQura\Core\BusinessLogic\Domain\WidgetConfiguration\Models\WidgetConfiguration;

/**
 * Class WidgetConfigurationController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration
 */
class WidgetConfigurationController
{
    /**
     * Gets the widget configurator.
     *
     * @return WidgetConfiguratorResponse
     */
    public function getWidgetConfigurator(): WidgetConfiguratorResponse
    {
        return new WidgetConfiguratorResponse();
    }

    /**
     * Gets active widget configuration.
     *
     * @return WidgetConfigurationResponse
     */
    public function getWidgetConfiguration(): WidgetConfigurationResponse
    {
        return new WidgetConfigurationResponse(
            new WidgetConfiguration(
                true,
                false,
                true,
                false,
                'key key',
                null,
                null
            )
        );
    }

    /**
     * Saves a new widget configuration.
     *
     * @param WidgetConfigurationRequest $request
     *
     * @return SuccessfulWidgetConfigurationResponse
     */
    public function saveWidgetConfiguration(WidgetConfigurationRequest $request): SuccessfulWidgetConfigurationResponse
    {
        return new SuccessfulWidgetConfigurationResponse();
    }
}
