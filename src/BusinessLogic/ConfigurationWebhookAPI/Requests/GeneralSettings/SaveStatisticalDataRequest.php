<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;

/**
 * Class SaveStatisticalDataRequest.
 *
 * Whether SeQura may collect statistical data is configured together with the
 * general settings, while it is stored on its own. A payload that does not carry the
 * value leaves the stored one alone rather than turning the collection off.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings
 */
class SaveStatisticalDataRequest extends ConfigurationWebhookRequest
{
    /**
     * @var bool|null $sendStatisticalData
     */
    private $sendStatisticalData;

    /**
     * @param bool|null $sendStatisticalData
     */
    public function __construct(?bool $sendStatisticalData)
    {
        $this->sendStatisticalData = $sendStatisticalData;
    }

    /**
     * @return bool|null
     */
    public function getSendStatisticalData(): ?bool
    {
        return $this->sendStatisticalData;
    }

    /**
     * @param mixed[] $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): object
    {
        return new self(
            \array_key_exists('isSendStatisticalData', $payload) ? (bool)$payload['isSendStatisticalData'] : null
        );
    }
}
