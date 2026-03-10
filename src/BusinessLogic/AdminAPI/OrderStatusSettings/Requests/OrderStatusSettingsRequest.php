<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;

/**
 * Class OrderStatusSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests
 */
class OrderStatusSettingsRequest extends Request
{
    use SaveOrderStatusSettingsRequestTrait;
}
