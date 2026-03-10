<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI;

use SeQura\Core\BusinessLogic\AdminAPI\Aspects\ErrorHandlingAspect;
use SeQura\Core\BusinessLogic\AdminAPI\Aspects\StoreContextAspect;
use SeQura\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Controller\ConfigurationWebhookController;

/**
 * Class ConfigurationWebhookAPI
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI
 */
class ConfigurationWebhookAPI
{
    /**
     * Gets a configuration webhook handler with error handling and store context aspects applied.
     *
     * @param string $storeId
     *
     * @return Aspects
     */
    public static function configurationHandler(string $storeId = ''): Aspects
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(ConfigurationWebhookController::class);
    }
}
