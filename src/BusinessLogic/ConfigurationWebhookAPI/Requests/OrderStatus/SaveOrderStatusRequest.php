<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\OrderStatus;

use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests\SaveOrderStatusSettingsRequestTrait;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;

/**
 * Class SaveOrderStatusRequest.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\OrderStatus
 */
class SaveOrderStatusRequest extends ConfigurationWebhookRequest
{
    use SaveOrderStatusSettingsRequestTrait;

    /**
     * @param mixed[] $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): object
    {
        return new self($payload['orderStatusMappings'] ?? []);
    }
}
