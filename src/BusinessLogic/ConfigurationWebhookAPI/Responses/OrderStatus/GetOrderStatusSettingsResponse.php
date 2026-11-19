<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\OrderStatus;

use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\OrderStatusSettingsResponseTrait;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class GetOrderStatusSettingsServiceResponse.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\OrderStatus
 */
class GetOrderStatusSettingsResponse extends Response
{
    use OrderStatusSettingsResponseTrait;
}
