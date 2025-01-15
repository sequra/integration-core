<?php

namespace SeQura\Core\BusinessLogic\WebhookAPI;

use SeQura\Core\BusinessLogic\AdminAPI\Aspects\StoreContextAspect;
use SeQura\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use SeQura\Core\BusinessLogic\WebhookAPI\Controller\WebhookController;

/**
 * Class WebhookAPI
 *
 * @package SeQura\Core\BusinessLogic\WebhookAPI
 */
class WebhookAPI
{
    /**
     * @return Aspects
     */
    public static function webhookHandler(string $storeId = ''): Aspects
    {
        return Aspects
            ::run(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(WebhookController::class);
    }
}
