<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\OrderStatus;

use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\ShopOrderStatusResponseTrait;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class GetOrderStatusListResponse.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\OrderStatus
 */
class GetOrderStatusListResponse extends Response
{
    use ShopOrderStatusResponseTrait;
}
