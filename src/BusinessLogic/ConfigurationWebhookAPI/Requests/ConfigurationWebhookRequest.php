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
     * @param array $payload
     *
     * @return object
     */
    public static abstract function fromPayload(array $payload): object;
}
