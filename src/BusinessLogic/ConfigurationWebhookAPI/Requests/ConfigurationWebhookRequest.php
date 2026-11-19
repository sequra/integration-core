<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests;

/**
 * Class ConfigurationWebhookRequest.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests
 */
abstract class ConfigurationWebhookRequest
{
    /**
     * @param mixed[] $payload
     *
     * @return object
     */
    abstract public static function fromPayload(array $payload): object;
}
