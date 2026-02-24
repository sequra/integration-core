<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SellingCountries;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class SellingCountriesResponse.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SellingCountries
 */
class SellingCountriesResponse extends Response
{
    /**
     * @var string[]
     */
    protected $sellingCountries;

    /**
     * @param string[] $sellingCountries
     */
    public function __construct(array $sellingCountries)
    {
        $this->sellingCountries = $sellingCountries;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_values($this->sellingCountries);
    }
}
