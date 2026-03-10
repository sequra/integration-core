<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;

/**
 * Class SaveSellingCountriesRequest.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings
 */
class SaveSellingCountriesRequest extends ConfigurationWebhookRequest
{
    /**
     * @var string[] $sellingCountries
     */
    private $sellingCountries;

    /**
     * @param string[] $sellingCountries
     */
    public function __construct(array $sellingCountries)
    {
        $this->sellingCountries = $sellingCountries;
    }

    /**
     * @return string[]
     */
    public function getSellingCountries(): array
    {
        return $this->sellingCountries;
    }

    /**
     * @param mixed[] $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): object
    {
        return new self($payload['sellingCountries'] ?? []);
    }
}
